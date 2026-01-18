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
        Schema::table('app_bank_manager_fixed_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('operation_sub_category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_bank_manager_fixed_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('operation_sub_category_id')->nullable(false)->change();
        });
    }
};
