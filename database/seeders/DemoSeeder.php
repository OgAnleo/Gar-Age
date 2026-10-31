<?php

namespace Database\Seeders;

use App\Models\User;
use DateTimeImmutable;
use GarAge\Billing\Application\GenerateDueFees;
use GarAge\Billing\Application\ListFees;
use GarAge\Billing\Application\MarkFeePaid;
use GarAge\Billing\Domain\Fee;
use GarAge\Contracts\Application\ContractInput;
use GarAge\Contracts\Application\CreateContract;
use GarAge\Customers\Application\CreateCustomer;
use GarAge\Customers\Application\CustomerInput;
use GarAge\Pricing\Application\UpdatePricingConditions;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\DeleteBusiness;
use GarAge\Tenancy\Application\ListOwnedBusinesses;
use GarAge\Tenancy\Application\RegisterBusiness;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Application\CreateUnit;
use GarAge\Units\Application\UnitInput;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * A demo business full of data relative to today: occupied and free units,
 * contracts of every booking mode, and paid, pending and overdue fees.
 * Everything goes through the use cases, so prices and fees follow the
 * business rules. Running it again rebuilds the demo business from scratch
 * and restores the demo account (the public demo runs it every night).
 */
class DemoSeeder extends Seeder
{
    private const string TAX_ID_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /** code => [type, m², daily rate] */
    private const array UNITS = [
        'G-01' => ['garage', '12.50', '5.50'],
        'G-02' => ['garage', '14', '6'],
        'G-03' => ['garage', '12.50', '5.50'],
        'G-04' => ['garage', '16', '6.50'],
        'G-05' => ['garage', '11', '4.80'],
        'G-06' => ['garage', '11', '4.80'],
        'T-01' => ['storage', '3', '1.40'],
        'T-02' => ['storage', '4.50', '1.80'],
        'T-03' => ['storage', '6', '2.20'],
        'T-04' => ['storage', '8', '2.60'],
        'T-05' => ['storage', '2', '1.10'],
        'T-06' => ['storage', '3', '1.40'],
    ];

    /** [name, DNI number, phone] */
    private const array CUSTOMERS = [
        ['Ana López García', 12345678, '612345678'],
        ['Carlos Martín Ruiz', 23456789, '623456789'],
        ['Lucía Fernández Gil', 34567890, '634567890'],
        ['Javier Sánchez Moreno', 45678901, '645678901'],
        ['Marta Romero Díaz', 56789012, '656789012'],
        ['Pablo Navarro Torres', 67890123, '667890123'],
        ['Elena Castro Vidal', 78901234, '678901234'],
        ['Sergio Ortega Ramos', 89012345, '689012345'],
    ];

    /** Customers who leave their latest fee unpaid past its due date. */
    private const array LATE_PAYERS = [2, 4];

    public function run(
        RegisterBusiness $registerBusiness,
        ListOwnedBusinesses $listOwnedBusinesses,
        DeleteBusiness $deleteBusiness,
        UpdatePricingConditions $updatePricing,
        CreateUnit $createUnit,
        CreateCustomer $createCustomer,
        CreateContract $createContract,
        GenerateDueFees $generateDueFees,
        ListFees $listFees,
        MarkFeePaid $markFeePaid,
    ): void {
        $user = $this->demoUser();
        $userId = new UserId($user->id);

        foreach ($listOwnedBusinesses->handle($userId) as $previous) {
            $deleteBusiness->handle($previous->id, $userId);
        }

        $tenantId = $registerBusiness->handle('Trasteros Demo', $userId)->id;
        $updatePricing->handle($tenantId, weekly: 5, monthly: 15, annual: 25);

        /** @var array<string, int> $units code => id */
        $units = [];
        foreach (self::UNITS as $code => [$type, $size, $rate]) {
            $units[$code] = $createUnit->handle($tenantId, new UnitInput($code, $type, $size, $rate))->value;
        }

        $customers = [];
        foreach (self::CUSTOMERS as [$name, $number, $phone]) {
            $customers[] = $createCustomer->handle($tenantId, new CustomerInput(
                name: $name,
                taxId: $number.self::TAX_ID_LETTERS[$number % 23],
                email: Str::slug($name, '.').'@example.com',
                phone: $phone,
            ))->value;
        }

        $today = now()->startOfDay()->toDateTimeImmutable();
        $day = fn (int $offset): string => $today->modify("{$offset} days")->format('Y-m-d');
        $contract = fn (string $unit, int $customer, string $mode, ?int $startOffset, ?int $periods = null, array $days = []) => $createContract->handle(
            $tenantId,
            new ContractInput($units[$unit], $customers[$customer], $mode, $startOffset === null ? null : $day($startOffset), $periods, array_values($days)),
        )->value;

        $contractIds = [
            $contract('G-01', 0, 'monthly', -150),
            $contract('G-02', 1, 'annual', -200),
            $contract('G-03', 2, 'monthly', -70),
            $contract('G-04', 3, 'weekly', -20, 6),
            $contract('T-01', 4, 'monthly', -100),
            $contract('T-02', 5, 'monthly', -40),
            $contract('T-03', 6, 'days', null, days: [$day(-3), $day(-2), $day(0), $day(5), $day(6)]),
            $contract('T-04', 7, 'weekly', -60, 4), // already finished
            $contract('G-05', 0, 'monthly', 10),    // starts soon
        ];

        $generateDueFees->handle($today, $tenantId);
        $this->payFees($tenantId, $today, $listFees, $markFeePaid, array_map(
            fn (int $customer): int => $contractIds[$customer], // each customer's first contract
            self::LATE_PAYERS,
        ));
    }

    /**
     * The demo account with its published credentials, no second factor and no passkeys.
     */
    private function demoUser(): User
    {
        $user = User::firstOrNew(['email' => config('demo.email')]);
        $user->forceFill([
            'name' => 'Demo',
            'password' => config('demo.password'),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $user->passkeys()->delete();

        return $user;
    }

    /**
     * Pay every fee past its grace days three days after its period started,
     * except the latest one of each late payer's contract.
     *
     * @param  list<int>  $lateContractIds
     */
    private function payFees(TenantId $tenantId, DateTimeImmutable $today, ListFees $listFees, MarkFeePaid $markFeePaid, array $lateContractIds): void
    {
        $leftUnpaid = [];
        foreach ($listFees->handle($tenantId) as $fee) { // newest period first
            $contractId = $fee->contractId->value;
            if ($this->isPastDue($fee, $today) && in_array($contractId, $lateContractIds, true) && ! isset($leftUnpaid[$contractId])) {
                $leftUnpaid[$contractId] = true;
            } elseif ($this->isPastDue($fee, $today)) {
                $markFeePaid->handle($tenantId, $fee->id(), $fee->period->start->modify('+3 days'));
            }
        }
    }

    private function isPastDue(Fee $fee, DateTimeImmutable $today): bool
    {
        return $fee->dueOn() < $today;
    }
}
