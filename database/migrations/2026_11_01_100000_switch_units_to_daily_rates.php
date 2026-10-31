<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Units are priced per day (bookings are priced from it); the business sets
 * the discount of each recurring booking mode.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('daily_price_cents')->default(0);
        });
        DB::table('units')->update(['daily_price_cents' => DB::raw('ROUND(monthly_price_cents / 30.0)')]);
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('monthly_price_cents');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedTinyInteger('weekly_discount_percent')->default(0);
            $table->unsignedTinyInteger('monthly_discount_percent')->default(0);
            $table->unsignedTinyInteger('annual_discount_percent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['weekly_discount_percent', 'monthly_discount_percent', 'annual_discount_percent']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('monthly_price_cents')->default(0);
        });
        DB::table('units')->update(['monthly_price_cents' => DB::raw('daily_price_cents * 30')]);
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('daily_price_cents');
        });
    }
};
