<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contracts get a booking mode (annual, monthly, weekly or specific days), a
 * number of periods (null: indefinite) and a price per billing period.
 * starts_on/ends_on keep the whole span, so date queries stay simple.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('mode')->default('monthly');
            $table->unsignedSmallInteger('period_count')->nullable();
            $table->unsignedInteger('price_per_period_cents')->default(0);
        });

        // Existing contracts were monthly: keep their price, and turn a fixed end into whole months.
        foreach (DB::table('contracts')->get() as $contract) {
            $start = CarbonImmutable::parse($contract->starts_on);
            $periods = null;
            $end = null;

            if ($contract->ends_on !== null) {
                $periods = 1;
                while ($start->addMonthsNoOverflow($periods)->subDay()->toDateString() < $contract->ends_on) {
                    $periods++;
                }
                $end = $start->addMonthsNoOverflow($periods)->subDay()->toDateString();
            }

            DB::table('contracts')->where('id', $contract->id)->update([
                'period_count' => $periods,
                'ends_on' => $end,
                'price_per_period_cents' => $contract->monthly_price_cents,
            ]);
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('monthly_price_cents');
        });

        Schema::create('contract_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->date('day');

            $table->unique(['contract_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_days');

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedInteger('monthly_price_cents')->default(0);
        });
        DB::table('contracts')->update(['monthly_price_cents' => DB::raw('price_per_period_cents')]);
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['mode', 'period_count', 'price_per_period_cents']);
        });
    }
};
