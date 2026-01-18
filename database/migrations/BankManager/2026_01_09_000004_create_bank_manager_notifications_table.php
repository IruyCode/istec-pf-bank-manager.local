<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_manager_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Tipo de notificação
            $table->enum('type', [
                'expense_recent',      // Despesas recentes não registradas
                'expense_fixed',       // Despesas fixas próximas
                'investment',          // Lembrete de atualizar investimentos
                'debtor',             // Cobrança de devedores
                'debt',               // Parcelas de dívidas
                'goal',               // Contribuição para metas
                'spending'            // Alertas de gastos
            ]);
            
            // Título e mensagem
            $table->string('title');
            $table->text('message');
            
            // Contexto único para evitar duplicação
            $table->string('context')->unique();
            
            // Dados adicionais (JSON)
            $table->json('data')->nullable();
            
            // Link de redirecionamento
            $table->string('link')->nullable();
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            
            // Quando foi disparada
            $table->timestamp('triggered_at')->useCurrent();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_read']);
            $table->index('triggered_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_manager_notifications');
    }
};
