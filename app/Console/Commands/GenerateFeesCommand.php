<?php

namespace App\Console\Commands;

use GarAge\Billing\Application\GenerateDueFees;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Console adapter: issues the fees of every tenant whose billing period has
 * started (scheduled daily in routes/console.php).
 */
#[Signature('fees:generate {date? : Day as YYYY-MM-DD (defaults to today)}')]
#[Description('Issue the fee of every billing period started by the day')]
class GenerateFeesCommand extends Command
{
    public function handle(GenerateDueFees $generateDueFees): int
    {
        $argument = $this->argument('date');

        try {
            $day = is_string($argument) ? DateRange::day($argument) : now()->toDateTimeImmutable();
        } catch (InvalidValue) {
            $this->error('The date must be written as YYYY-MM-DD.');

            return self::INVALID;
        }

        $issued = $generateDueFees->handle($day);
        $this->info("{$issued} fee(s) issued up to {$day->format('Y-m-d')}.");

        return self::SUCCESS;
    }
}
