<?php

namespace App\Console\Commands;

use GarAge\Billing\Application\GenerateMonthlyFees;
use GarAge\Billing\Domain\YearMonth;
use GarAge\Shared\Domain\InvalidValue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Console adapter: issues the monthly fees of every tenant (scheduled daily in
 * routes/console.php, so contracts created mid-month get their fee too).
 */
#[Signature('fees:generate {month? : Month as YYYY-MM (defaults to the current month)}')]
#[Description('Issue the monthly fee of every running contract')]
class GenerateFeesCommand extends Command
{
    public function handle(GenerateMonthlyFees $generateMonthlyFees): int
    {
        $argument = $this->argument('month');

        try {
            $month = is_string($argument) ? new YearMonth($argument) : YearMonth::containing(now()->toDateTimeImmutable());
        } catch (InvalidValue) {
            $this->error('The month must be written as YYYY-MM.');

            return self::INVALID;
        }

        $issued = $generateMonthlyFees->handle($month);
        $this->info("{$issued} fee(s) issued for {$month->value}.");

        return self::SUCCESS;
    }
}
