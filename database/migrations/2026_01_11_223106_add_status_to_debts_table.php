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
        Schema::table('app_bank_manager_debts', function (Blueprint $table) {
            $table->enum('status', ['not_started', 'active', 'paused', 'completed'])
                  ->default('not_started')
                  ->after('total_amount');
            $table->unsignedBigInteger('fixed_expense_id')->nullable()->after('status');
            
            $table->foreign('fixed_expense_id')
                  ->references('id')
                  ->on('app_bank_manager_fixed_expenses')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_debts', function (Blueprint $table) {
            $table->dropForeign(['fixed_expense_id']);
            $table->dropColumn(['status', 'fixed_expense_id']);
        });
    }
};
