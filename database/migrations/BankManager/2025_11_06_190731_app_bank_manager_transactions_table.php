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
        Schema::create('app_bank_manager_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('description')->nullable();

            $table->foreignId('account_balance_id')
                ->constrained('app_bank_manager_account_balances')
                ->onDelete('cascade');

            $table->foreignId('operation_type_id')
                ->constrained('app_bank_manager_operation_types')
                ->onDelete('cascade');

            $table->foreignId('operation_sub_category_id')
                ->constrained('app_bank_manager_operation_sub_categories')
                ->onDelete('cascade');

            $table->unsignedBigInteger('spending_context_id')->nullable();
            $table->foreign('spending_context_id')
                ->references('id')
                ->on('app_bank_manager_spending_contexts')
                ->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_bank_manager_transactions');
    }
};
