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
        // Adicionar operation_type_id à tabela de categorias
        Schema::table('app_bank_manager_operation_categories', function (Blueprint $table) {
            $table->foreignId('operation_type_id')
                ->nullable()
                ->after('name')
                ->constrained('app_bank_manager_operation_types', 'id', indexName: 'op_cat_type_fk')
                ->onDelete('restrict');
        });

        // Adicionar operation_type_id à tabela de subcategorias
        Schema::table('app_bank_manager_operation_sub_categories', function (Blueprint $table) {
            $table->foreignId('operation_type_id')
                ->nullable()
                ->after('operation_category_id')
                ->constrained('app_bank_manager_operation_types', 'id', indexName: 'op_subcat_type_fk')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_operation_categories', function (Blueprint $table) {
            $table->dropForeign(['operation_type_id']);
            $table->dropColumn('operation_type_id');
        });

        Schema::table('app_bank_manager_operation_sub_categories', function (Blueprint $table) {
            $table->dropForeign(['operation_type_id']);
            $table->dropColumn('operation_type_id');
        });
    }
};
