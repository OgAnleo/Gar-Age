<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A fee covers a billing period (a week, a month, a year or a booking of
 * specific days) instead of a calendar month.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
        });

        foreach (DB::table('fees')->get() as $fee) {
            $month = CarbonImmutable::parse("{$fee->month}-01");
            DB::table('fees')->where('id', $fee->id)->update([
                'period_start' => $month->toDateString(),
                'period_end' => $month->endOfMonth()->toDateString(),
            ]);
        }

        Schema::table('fees', function (Blueprint $table) {
            $table->dropUnique(['contract_id', 'month']);
            $table->dropIndex(['tenant_id', 'month']);
            $table->dropColumn('month');

            // One fee per contract and period, even if generation runs twice at once.
            $table->unique(['contract_id', 'period_start']);
            $table->index(['tenant_id', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->char('month', 7)->nullable();
        });
        DB::table('fees')->update(['month' => DB::raw('substr(period_start, 1, 7)')]);
        Schema::table('fees', function (Blueprint $table) {
            $table->dropUnique(['contract_id', 'period_start']);
            $table->dropIndex(['tenant_id', 'period_start']);
            $table->dropColumn(['period_start', 'period_end']);
            $table->unique(['contract_id', 'month']);
            $table->index(['tenant_id', 'month']);
        });
    }
};
