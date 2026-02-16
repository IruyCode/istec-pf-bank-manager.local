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
        Schema::table('app_bank_manager_debtors', function (Blueprint $table) {
            // Adiciona user_id para multi-usuário (verifica se já existe)
            if (!Schema::hasColumn('app_bank_manager_debtors', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('id')
                    ->constrained('users')
                    ->onDelete('cascade');
            }

            // Adiciona transaction_id para vincular quando o devedor pagar
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('paid_at')
                ->constrained('app_bank_manager_transactions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_debtors', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
            
            // Só remove user_id se esta migration o criou
            if (Schema::hasColumn('app_bank_manager_debtors', 'user_id')) {
                // Verifica se há uma FK antes de tentar remover
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // FK pode ter sido criada por outra migration
                }
            }
        });
    }
};
