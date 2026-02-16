<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove operation_type_id de operation_sub_categories pois é redundante.
     * O tipo já é herdado via operation_category -> operation_type
     */
    public function up(): void
    {
        Schema::table('app_bank_manager_operation_sub_categories', function (Blueprint $table) {
            // Remove a foreign key primeiro (usando o nome customizado do índice)
            $table->dropForeign('op_subcat_type_fk');
            // Remove a coluna
            $table->dropColumn('operation_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_operation_sub_categories', function (Blueprint $table) {
            $table->foreignId('operation_type_id')
                ->nullable()
                ->after('operation_category_id')
                ->constrained('app_bank_manager_operation_types', 'id', indexName: 'op_subcat_type_fk')
                ->onDelete('restrict');
        });
    }
};
