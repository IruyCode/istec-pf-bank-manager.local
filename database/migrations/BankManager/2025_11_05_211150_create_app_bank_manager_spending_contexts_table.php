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
        Schema::create('app_bank_manager_spending_contexts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // cada contexto pertence ao utilizador
            $table->string('name');
            $table->enum('type', ['trip', 'vacation', 'event', 'special', 'other'])->default('other');

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('budget', 10, 2)->nullable(); // orçamento opcional
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // FK opcional — se quiser garantir integridade
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_bank_manager_spending_contexts');
    }
};
