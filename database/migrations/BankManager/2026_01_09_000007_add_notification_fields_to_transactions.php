<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_bank_manager_transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            $table->date('transaction_date')->after('amount')->useCurrent();
            $table->boolean('is_recurring')->default(false)->after('transaction_date');
            $table->tinyInteger('due_day')->nullable()->after('is_recurring');
            $table->foreignId('debt_installment_id')->nullable()->after('due_day')->constrained('app_bank_manager_debt_installments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('app_bank_manager_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['debt_installment_id']);
            $table->dropColumn(['user_id', 'transaction_date', 'is_recurring', 'due_day', 'debt_installment_id']);
        });
    }
};
