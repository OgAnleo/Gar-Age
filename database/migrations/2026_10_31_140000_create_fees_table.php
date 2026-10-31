<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // A contract with fees cannot be deleted: the payment history is kept.
            $table->foreignId('contract_id')->constrained()->restrictOnDelete();
            $table->char('month', 7); // YYYY-MM
            $table->unsignedInteger('amount_cents');
            $table->date('paid_on')->nullable();
            $table->timestamps();

            // One fee per contract and month, even if generation runs twice at once.
            $table->unique(['contract_id', 'month']);
            $table->index(['tenant_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
