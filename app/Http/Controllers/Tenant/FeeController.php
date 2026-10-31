<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Presenters\FeePresenter;
use App\Http\Presenters\TenantLabels;
use GarAge\Billing\Application\GenerateMonthlyFees;
use GarAge\Billing\Application\ListFees;
use GarAge\Billing\Application\MarkFeePaid;
use GarAge\Billing\Application\MarkFeeUnpaid;
use GarAge\Billing\Domain\FeeId;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Contracts\Application\ListContracts;
use GarAge\Customers\Application\ListCustomers;
use GarAge\Units\Application\ListUnits;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: translates requests into Billing use cases.
 */
class FeeController extends TenantController
{
    /**
     * List the fees of the current tenant with their status today.
     */
    public function index(
        ListFees $listFees,
        ListContracts $listContracts,
        ListUnits $listUnits,
        ListCustomers $listCustomers,
        FeePresenter $presenter,
    ): Response {
        $tenantId = $this->tenantId();
        $labels = TenantLabels::from($listUnits->handle($tenantId), $listCustomers->handle($tenantId));

        return Inertia::render('tenant/fees/index', [
            'fees' => $presenter->listItems(
                $listFees->handle($tenantId),
                $listContracts->handle($tenantId),
                $labels,
                $this->today(),
            ),
            'current_month' => YearMonth::containing($this->today())->value,
        ]);
    }

    /**
     * Issue this month's fees of the current tenant now, without waiting for the scheduler.
     */
    public function generate(GenerateMonthlyFees $generateMonthlyFees): RedirectResponse
    {
        $issued = $generateMonthlyFees->handle(YearMonth::containing($this->today()), $this->tenantId());

        return $this->backToIndex($issued === 0
            ? 'Las cuotas de este mes ya estaban generadas.'
            : "Cuotas generadas: {$issued}.");
    }

    /**
     * Record today's payment of a fee.
     */
    public function pay(int $fee, MarkFeePaid $markFeePaid): RedirectResponse
    {
        $markFeePaid->handle($this->tenantId(), new FeeId($fee), $this->today());

        return $this->backToIndex('Cuota marcada como pagada.');
    }

    /**
     * Undo the payment of a fee.
     */
    public function unpay(int $fee, MarkFeeUnpaid $markFeeUnpaid): RedirectResponse
    {
        $markFeeUnpaid->handle($this->tenantId(), new FeeId($fee));

        return $this->backToIndex('Pago deshecho.');
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return $this->redirectWithToast('tenant.fees.index', $message);
    }
}
