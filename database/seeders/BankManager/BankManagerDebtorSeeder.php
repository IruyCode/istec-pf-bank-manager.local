<?php

namespace Database\Seeders\BankManager;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankManagerDebtorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $userId = 1;

        /* ============================================================
         | 1) CRIAR CONTAS BANCÁRIAS
         ============================================================ */
        DB::table('app_bank_manager_account_balances')->insert([
            [
                'user_id'         => $userId,
                'account_name'    => 'Conta Principal (Millenium)',
                'bank_name'       => 'Millennium',
                'account_type'    => 'personal',
                'current_balance' => 0.00,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'user_id'         => $userId,
                'account_name'    => 'Conta Empresarial',
                'bank_name'       => 'Santander',
                'account_type'    => 'business',
                'current_balance' => 0.00,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);

        /* ============================================================
         | 2) TIPOS DE OPERAÇÃO (income / expense)
         ============================================================ */
        $operationTypes = [
            'income',
            'expense',
        ];

        $operationTypeIds = [];
        
        foreach ($operationTypes as $type) {
            $operationTypeIds[$type] = DB::table('app_bank_manager_operation_types')
                ->insertGetId([
                    'operation_type' => $type,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }
        /* ============================================================
         | 3) CATEGORIAS PRINCIPAIS (macro)
         ============================================================ */
        $categories = [
            'Alimentação'        => 'expense',
            'Lazer'              => 'expense',
            'Saúde'              => 'expense',
            'Casa'               => 'expense',
            'Transporte'         => 'expense',
            'Assinaturas'        => 'expense',
            'Investimentos'      => 'expense', // compra
            'Metas'              => 'expense', // aporte

            'Receitas Gerais'    => 'income',
            'Investimentos (Retorno)' => 'income', // rendimento
            'Metas (Saques)'          => 'income', // sacar meta
            'Despesas-Fixas'          => 'expense',
            'Debitos'                 => 'expense',
            'Metas (Aportes)'         => 'expense',
        ];

        $categoryIds = [];
        foreach ($categories as $catName => $typeName) {
            $categoryIds[$catName] = DB::table('app_bank_manager_operation_categories')
                ->insertGetId([
                    'name' => $catName,
                    'operation_type_id' => $operationTypeIds[$typeName],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        /* ============================================================
         | 4) SUBCATEGORIAS (micro)
         ============================================================ */
        $subCategories = [

            // Alimentação
            'Alimentação' => [
                'Supermercado',
                'Restaurante',
                'Café',
                'Delivery',
            ],

            // Lazer
            'Lazer' => [
                'Cinema',
                'Eventos',
                'Jogos',
                'Bar / Noite',
                'Viagens',
            ],

            // Saúde
            'Saúde' => [
                'Farmácia',
                'Consultas',
                'Exames',
                'Academia',
            ],

            // Casa
            'Casa' => [
                'Aluguel',
                'Conta de Luz',
                'Água',
                'Internet',
                'Manutenção',
            ],

            // Transporte
            'Transporte' => [
                'Uber',
                'Combustível',
                'Metro/Autocarro',
                'Estacionamento',
            ],

            // Assinaturas
            'Assinaturas' => [
                'Netflix',
                'Spotify',
                'Amazon Prime',
                'Apple One',
                'Outras assinaturas',
            ],

            // Investimentos (Entrada/Saída de valores)
            'Investimentos' => [
                'Aporte inicial',
                'Retorno',
                'Retirada',
                'Ações',
                'ETFs',
                'Cripto',
                'Renda Fixa',
            ],

            // Metas (Guardar ou Sacar)
            'Metas' => [
                'Metas Ativas',
                'Fundo Emergência',
                'Casa Própria',
                'Aposentadoria',
                'Viagem',
                'Estudos',
            ],

            // Debitos
            'Debitos' => [
                'Devedores',
                'Dívidas',
            ],

            // Metas (Aportes)
            'Metas (Aportes)' => [
                'Metas Ativas',
            ],

            // Despesas-Fixas
            'Despesas-Fixas' => [
                'Pagar Despesa',
            ],

            // Receitas Gerais
            'Receitas Gerais' => [
                'Salário',
                'Renda Extra',
                'Prémios',
                'Reembolso',
            ],

            // Investimentos (Retorno)
            'Investimentos (Retorno)' => [
                'Dividendos',
                'Juros',
                'Rentabilidade',
            ],

            // Metas (Saques)
            'Metas (Saques)' => [
                'Saque Meta – Emergência',
                'Saque Meta – Objetivo',
            ],

            // Despesas-Fixas
            'Despesas-Fixas' => [
                'Pagar Despesa',
            ],

            // Debitos (Dívidas e Devedores)
            'Debitos' => [
                'Dividas',
                'Devedores',
            ],
        ];

        foreach ($subCategories as $categoryName => $subs) {
            foreach ($subs as $subName) {
                $categoryTypeId = $categories[$categoryName]; // 'expense' ou 'income'
                DB::table('app_bank_manager_operation_sub_categories')->insert([
                    'operation_category_id' => $categoryIds[$categoryName],
                    'operation_type_id' => $operationTypeIds[$categoryTypeId],
                    'name' => $subName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
