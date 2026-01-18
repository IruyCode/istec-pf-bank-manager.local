<?php

namespace App\Modules\BankManager\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Modules\BankManager\Models\AccountBalance;
use App\Modules\BankManager\Models\OperationType;
use App\Modules\BankManager\Models\OperationCategory;
use App\Modules\BankManager\Models\OperationSubCategory;
use App\Modules\BankManager\Models\FixedExpenses\FixedExpense;
use App\Modules\BankManager\Models\Transaction;
use App\Modules\BankManager\Models\SpendingContext;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getMonthlySummary($type, $year, $month, $accountTypeFilter = null)
    {
        $operationTypeId = ($type === 'income') ? 1 : 2; // 1: Income, 2: Expense

        return Transaction::whereHas('accountBalance', function ($query) use ($accountTypeFilter) {
            $query->where('user_id', Auth::id());
            if ($accountTypeFilter && in_array($accountTypeFilter, ['personal', 'business'])) {
                $query->where('account_type', $accountTypeFilter);
            }
        })
            ->where('operation_type_id', $operationTypeId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');
    }

    private function getCategorySummary($type, $year, $month, $accountTypeFilter = null)
    {
        $operationTypeId = ($type === 'income') ? 1 : 2; // 1: Income, 2: Expense

        return Transaction::select(
            DB::raw('SUM(app_bank_manager_transactions.amount) as total_amount'),
            'app_bank_manager_operation_categories.name as category_name'
        )
            ->join('app_bank_manager_operation_sub_categories', 'app_bank_manager_operation_sub_categories.id', '=', 'app_bank_manager_transactions.operation_sub_category_id')
            ->join('app_bank_manager_operation_categories', 'app_bank_manager_operation_categories.id', '=', 'app_bank_manager_operation_sub_categories.operation_category_id')
            ->whereHas('accountBalance', function ($query) use ($accountTypeFilter) {
                $query->where('user_id', Auth::id());
                if ($accountTypeFilter && in_array($accountTypeFilter, ['personal', 'business'])) {
                    $query->where('account_type', $accountTypeFilter);
                }
            })
            ->where('app_bank_manager_transactions.operation_type_id', $operationTypeId)
            ->whereYear('app_bank_manager_transactions.created_at', $year)
            ->whereMonth('app_bank_manager_transactions.created_at', $month)
            ->groupBy('app_bank_manager_operation_categories.name')
            ->orderByDesc('total_amount')
            ->get();
    }

    private function getExpenseChartData($year, $month, $accountTypeFilter = null, $categoryId = null)
    {
        $operationTypeId = 2; // Expense

        $query = Transaction::select(
            DB::raw('SUM(app_bank_manager_transactions.amount) as total_amount')
        )
            ->whereHas('accountBalance', function ($q) use ($accountTypeFilter) {
                $q->where('user_id', Auth::id());
                if ($accountTypeFilter && in_array($accountTypeFilter, ['personal', 'business'])) {
                    $q->where('account_type', $accountTypeFilter);
                }
            })
            ->where('app_bank_manager_transactions.operation_type_id', $operationTypeId)
            ->whereYear('app_bank_manager_transactions.created_at', $year)
            ->whereMonth('app_bank_manager_transactions.created_at', $month);

        if ($categoryId) {
            // Filtrar por Subcategoria
            $query->join('app_bank_manager_operation_sub_categories', 'app_bank_manager_operation_sub_categories.id', '=', 'app_bank_manager_transactions.operation_sub_category_id')
                ->where('app_bank_manager_operation_sub_categories.operation_category_id', $categoryId)
                ->groupBy('app_bank_manager_operation_sub_categories.name')
                ->addSelect('app_bank_manager_operation_sub_categories.name as label_name');
        } else {
            // Filtrar por Categoria
            $query->join('app_bank_manager_operation_sub_categories', 'app_bank_manager_operation_sub_categories.id', '=', 'app_bank_manager_transactions.operation_sub_category_id')
                ->join('app_bank_manager_operation_categories', 'app_bank_manager_operation_categories.id', '=', 'app_bank_manager_operation_sub_categories.operation_category_id')
                ->groupBy('app_bank_manager_operation_categories.name')
                ->addSelect('app_bank_manager_operation_categories.name as label_name');
        }

        return $query->orderByDesc('total_amount')->get();
    }

    private function getFixedExpenseSummary($year, $month, $accountTypeFilter = null)
    {
        // Despesas fixas não estão diretamente ligadas a account_type, mas podemos filtrar as contas ativas
        // para obter um valor mais preciso se necessário. Por simplicidade, manteremos o total de despesas fixas ativas.
        // Se a intenção for filtrar as despesas fixas que *foram pagas* por um tipo de conta, a lógica seria mais complexa.
        // Assumindo que o usuário quer o total de despesas fixas cadastradas.
        return FixedExpense::where('is_active', true)
            ->sum('amount');
    }

    private function getAverageMonthlyExpense($year, $accountTypeFilter = null)
    {
        $currentMonth = now()->month;
        $totalMonths = 0;
        $totalExpense = 0;

        for ($month = 1; $month < $currentMonth; $month++) {
            $expense = $this->getMonthlySummary('expense', $year, $month, $accountTypeFilter);
            if ($expense > 0) {
                $totalExpense += $expense;
                $totalMonths++;
            }
        }

        return $totalMonths > 0 ? $totalExpense / $totalMonths : 0;
    }

    private function getIncomeChartData($year, $month, $accountTypeFilter = null)
    {
        return Transaction::select(
            DB::raw('SUM(app_bank_manager_transactions.amount) as total_amount'),
            'app_bank_manager_operation_sub_categories.name as label_name'
        )
            ->join(
                'app_bank_manager_operation_sub_categories',
                'app_bank_manager_operation_sub_categories.id',
                '=',
                'app_bank_manager_transactions.operation_sub_category_id'
            )
            ->whereHas('accountBalance', function ($q) use ($accountTypeFilter) {
                $q->where('user_id', Auth::id());
                if ($accountTypeFilter && in_array($accountTypeFilter, ['personal', 'business'])) {
                    $q->where('account_type', $accountTypeFilter);
                }
            })
            ->where('app_bank_manager_transactions.operation_type_id', 1) // income
            ->whereYear('app_bank_manager_transactions.created_at', $year)
            ->whereMonth('app_bank_manager_transactions.created_at', $month)
            ->groupBy('app_bank_manager_operation_sub_categories.name')
            ->orderByDesc('total_amount')
            ->get();
    }


    public function index(Request $request)
    {
        $operationTypes = OperationType::select('id', 'operation_type')->get();
        $operationCategories = OperationCategory::select('id', 'name', 'operation_type_id')->get();
        $operationSubCategories = OperationSubCategory::select('id', 'name', 'operation_category_id', 'operation_type_id')->get();

        $accountTypeFilter = $request->input('filter', 'total'); // 'total', 'personal', 'business'

        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Define o filtro para as funções de resumo
        $filter = ($accountTypeFilter === 'total') ? null : $accountTypeFilter;

        // 1. Resumo Financeiro
        $totalIncome = $this->getMonthlySummary('income', $currentYear, $currentMonth, $filter);
        $totalExpense = $this->getMonthlySummary('expense', $currentYear, $currentMonth, $filter);
        $fixedExpenseTotal = $this->getFixedExpenseSummary($currentYear, $currentMonth, $filter);
        $averageMonthlyExpense = $this->getAverageMonthlyExpense($currentYear, $filter);

        // 2. Dados para Gráficos (Mês Atual)
        $expenseCategoryId = $request->input('expense_category_id');

        $expenseChartData = $this->getExpenseChartData($currentYear, $currentMonth, $filter, $expenseCategoryId);

        $incomeChartData = $this->getIncomeChartData($currentYear, $currentMonth, $filter);
        $incomeLabels = $incomeChartData->pluck('label_name');
        $incomeValues = $incomeChartData->pluck('total_amount');

        $expenseLabels = $expenseChartData->pluck('label_name');
        $expenseValues = $expenseChartData->pluck('total_amount');
        $operationCategoriesFilter = $operationCategories
            ->filter(fn($c) => !str_ends_with($c->name, '_Income') && !str_ends_with($c->name, '_Expenses'))
            ->values()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]);

        $filteredTotalExpense = $expenseValues->sum();

        $userId = Auth::id();

        // 3. Obter todas as contas ativas do utilizador
        $accountsQuery = AccountBalance::where('user_id', $userId)
            ->where('is_active', true);

        // Aplica o filtro de tipo de conta nas contas ativas
        if ($filter) {
            $accountsQuery->where('account_type', $filter);
        }

        $accounts = $accountsQuery->get();

        // 4. Calcular saldos (apenas das contas filtradas)
        $totalBalance = $accounts->sum('current_balance');
        // Mantemos os saldos totais para exibição no topo, se necessário, mas o foco é o filtro
        $allAccounts = AccountBalance::where('user_id', $userId)->where('is_active', true)->get();
        $personalBalance = $allAccounts->where('account_type', 'personal')->sum('current_balance');
        $businessBalance = $allAccounts->where('account_type', 'business')->sum('current_balance');

        // 5. Obter transações recentes (filtradas)
        $transactionsQuery = DB::table('app_bank_manager_transactions')
            ->join('app_bank_manager_operation_sub_categories', 'app_bank_manager_operation_sub_categories.id', '=', 'app_bank_manager_transactions.operation_sub_category_id')
            ->join('app_bank_manager_operation_types', 'app_bank_manager_operation_types.id', '=', 'app_bank_manager_transactions.operation_type_id')
            ->join('app_bank_manager_account_balances', 'app_bank_manager_account_balances.id', '=', 'app_bank_manager_transactions.account_balance_id')
            ->select(
                'app_bank_manager_transactions.*',
                'app_bank_manager_operation_types.operation_type as type_name',
                'app_bank_manager_operation_sub_categories.name as sub_category_name',
                'app_bank_manager_account_balances.user_id'
            )
            ->where('app_bank_manager_account_balances.user_id', $userId);

        // Aplica o filtro de tipo de conta nas transações
        if ($filter) {
            $transactionsQuery->where('app_bank_manager_account_balances.account_type', $filter);
        }

        $transactions = $transactionsQuery->orderByDesc('app_bank_manager_transactions.created_at')
            ->limit(10)
            ->get();

        $expenseCategories = OperationCategory::whereIn('id', function ($q) use ($currentYear, $currentMonth, $filter) {
            $q->from('app_bank_manager_transactions as t')
                ->join('app_bank_manager_operation_sub_categories as s', 's.id', '=', 't.operation_sub_category_id')
                ->join('app_bank_manager_account_balances as a', 'a.id', '=', 't.account_balance_id')
                ->whereYear('t.created_at', $currentYear)
                ->whereMonth('t.created_at', $currentMonth)
                ->where('t.operation_type_id', 2); // apenas despesas

            // aplica filtro de tipo de conta (pessoal/empresa)
            if ($filter) {
                $q->where('a.account_type', $filter);
            }

            $q->select('s.operation_category_id');
        })
            ->get();

        // 6. Obter contexto de gastos específico de evento ou viagem, se existir
        $activeContext = SpendingContext::where('user_id', $userId)
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        $contextTotalSpent = 0;
        $contextBudget = null;
        $contextPercentUsed = null;

        if ($activeContext) {
            $contextTotalSpent = Transaction::where('spending_context_id', $activeContext->id)
                ->sum('amount');

            $contextBudget = $activeContext->budget;

            if ($contextBudget) {
                $contextPercentUsed = ($contextTotalSpent / $contextBudget) * 100;
            }
        }

        $today = now()->toDateString();

        $activeContext = SpendingContext::where('user_id', $userId)
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        $upcomingContexts = SpendingContext::where('user_id', $userId)
            ->where('is_active', 1)
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date')
            ->get();
        $contextTransactions = Transaction::where('spending_context_id', $activeContext->id ?? null)->get();

        // Retorna a view com os dados
        return view('bankmanager::dashboard.index', [
            'accounts' => $accounts, // Contas filtradas
            'transactions' => $transactions, // Transações filtradas
            'totalBalance' => $totalBalance, // Saldo total das contas filtradas
            'personalBalance' => $personalBalance, // Saldo pessoal total (não filtrado)
            'businessBalance' => $businessBalance, // Saldo empresarial total (não filtrado)
            'operationTypes' => $operationTypes,
            'operationCategories' => $operationCategories,
            'operationSubCategories' => $operationSubCategories,
            'operationCategoriesFilter' => $operationCategoriesFilter,

            'accountBalance'  => AccountBalance::where('user_id', $userId)->get(),
            'fixedExpenses'   => FixedExpense::where('is_active', true)
                ->with(['payments' => function($query) {
                    $query->orderBy('year', 'desc')
                          ->orderBy('month', 'desc')
                          ->limit(12); // últimos 12 meses
                }])
                ->get(),

            // Novos dados para o resumo e gráficos (já filtrados)
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'fixedExpenseTotal' => $fixedExpenseTotal,
            'averageMonthlyExpense' => $averageMonthlyExpense,
            'expenseLabels' => $expenseLabels,
            'expenseValues' => $expenseValues,
            'incomeLabels' => $incomeLabels,
            'incomeValues' => $incomeValues,
            'accountTypeFilter' => $accountTypeFilter, // Passa o filtro atual para a view
            'expenseCategoryId' => $expenseCategoryId, // Passa o ID da categoria de despesa para a view
            'expenseCategories' => $expenseCategories, // Passa todas as categorias de despesa filtradas
            'filteredTotalExpense' => $filteredTotalExpense,

            'activeContext' => $activeContext,
            'contextTotalSpent' => $contextTotalSpent,
            'contextBudget' => $contextBudget,
            'contextPercentUsed' => $contextPercentUsed,
            'activeContext' => $activeContext,
            'upcomingContexts' => $upcomingContexts,
            'contextTransactions' => $contextTransactions,
        ]);
    }
}
