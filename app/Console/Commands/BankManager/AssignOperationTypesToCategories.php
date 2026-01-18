<?php

namespace App\Console\Commands\BankManager;

use Illuminate\Console\Command;
use App\Modules\BankManager\Models\OperationCategory;
use App\Modules\BankManager\Models\OperationSubCategory;
use App\Modules\BankManager\Models\OperationType;

class AssignOperationTypesToCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank-manager:assign-operation-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atribui tipos de operação (receita/despesa) às categorias e subcategorias existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando atribuição de tipos às categorias e subcategorias...');

        // Buscar os tipos de operação
        $incomeType = OperationType::where('operation_type', 'income')->first();
        $expenseType = OperationType::where('operation_type', 'expense')->first();

        if (!$incomeType || !$expenseType) {
            $this->error('Tipos de operação não encontrados na base de dados!');
            return 1;
        }

        // Categorias típicas de RECEITA
        $incomeKeywords = [
            'receita', 'salário', 'renda', 'rendimento', 'venda', 
            'freelance', 'comissão', 'bônus', 'prêmio'
        ];

        $categories = OperationCategory::whereNull('operation_type_id')->get();
        
        $this->info("Encontradas {$categories->count()} categorias sem tipo definido.");

        foreach ($categories as $category) {
            $isIncome = false;
            
            // Verificar se o nome da categoria contém palavras-chave de receita
            foreach ($incomeKeywords as $keyword) {
                if (stripos($category->name, $keyword) !== false) {
                    $isIncome = true;
                    break;
                }
            }

            // Perguntar ao usuário
            $type = $this->choice(
                "Qual o tipo da categoria '{$category->name}'?",
                ['Receita', 'Despesa'],
                $isIncome ? 0 : 1
            );

            $typeId = ($type === 'Receita') ? $incomeType->id : $expenseType->id;
            
            $category->operation_type_id = $typeId;
            $category->save();

            // Atualizar todas as subcategorias desta categoria com o mesmo tipo
            $subcategories = OperationSubCategory::where('operation_category_id', $category->id)
                ->whereNull('operation_type_id')
                ->get();

            foreach ($subcategories as $subcategory) {
                $subcategory->operation_type_id = $typeId;
                $subcategory->save();
            }

            $this->info("✓ Categoria '{$category->name}' definida como {$type} ({$subcategories->count()} subcategorias atualizadas)");
        }

        $this->info('Concluído! Todas as categorias e subcategorias foram atualizadas.');
        return 0;
    }
}
