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
        // Adicionar campo is_variable_amount em fixed_expenses
        Schema::table('app_bank_manager_fixed_expenses', function (Blueprint $table) {
            $table->boolean('is_variable_amount')->default(false)->after('amount');
        });

        // Adicionar campo amount_paid em fixed_expense_payments
        Schema::table('app_bank_manager_fixed_expense_payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->after('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_fixed_expenses', function (Blueprint $table) {
            $table->dropColumn('is_variable_amount');
        });

        Schema::table('app_bank_manager_fixed_expense_payments', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
