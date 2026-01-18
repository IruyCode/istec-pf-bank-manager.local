<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Investments
        if (!Schema::hasColumn('app_bank_manager_investments', 'user_id')) {
            Schema::table('app_bank_manager_investments', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        // Debtors
        if (!Schema::hasColumn('app_bank_manager_debtors', 'user_id')) {
            Schema::table('app_bank_manager_debtors', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }

        // Debts
        if (!Schema::hasColumn('app_bank_manager_debts', 'user_id')) {
            Schema::table('app_bank_manager_debts', function (Blueprint $table) {
                $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('app_bank_manager_investments', function (Blueprint $table) {
            if (Schema::hasColumn('app_bank_manager_investments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('app_bank_manager_debtors', function (Blueprint $table) {
            if (Schema::hasColumn('app_bank_manager_debtors', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('app_bank_manager_debts', function (Blueprint $table) {
            if (Schema::hasColumn('app_bank_manager_debts', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
