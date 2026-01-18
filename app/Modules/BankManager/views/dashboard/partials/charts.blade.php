<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

    <!-- Gráfico 1: Despesas por Categoria (Doughnut) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-chart-pie text-red-600"></i> Despesas por Categoria
            </h3>
            <form method="GET" action="{{ route('bank-manager.index') }}" id="expenseChartFilterForm">
                <input type="hidden" name="filter" value="{{ $accountTypeFilter }}">
                <select name="expense_category_id" id="expense_category_id"
                    onchange="document.getElementById('expenseChartFilterForm').submit()"
                    class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-1.5">
                    <option value="">Todas as Categorias</option>
                    @foreach ($expenseCategories as $category)
                        <option value="{{ $category->id }}" {{ $expenseCategoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="relative h-64">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>

    <!-- Gráfico 2: Receitas por Categoria (Doughnut) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-chart-pie text-green-600"></i> Receitas por Categoria
        </h3>
        <div class="relative h-64">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>

</div>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
    let expenseChart = null;
    let incomeChart = null;

    // Dados passados do Controller
    const expenseLabels = {!! json_encode($expenseLabels) !!};
    const expenseValues = {!! json_encode($expenseValues) !!};
    const incomeLabels = {!! json_encode($incomeLabels) !!};
    const incomeValues = {!! json_encode($incomeValues) !!};
    const totalExpense = Number({{ $totalExpense ?: 1 }});
    const totalIncome = Number({{ $totalIncome ?: 1 }});

    function safeNumber(value) {
        // Garante número válido ou 0
        return Number(value) || 0;
    }

    function initExpenseChart() {
        const ctx = document.getElementById('expenseChart');
        if (!ctx) return;

        if (expenseChart) expenseChart.destroy();

        const data = {
            labels: expenseLabels,
            datasets: [{
                label: 'Gastos',
                data: expenseValues.map(v => safeNumber(v)),
                backgroundColor: [
                    '#EF4444', '#F59E0B', '#10B981', '#3B82F6',
                    '#8B5CF6', '#EC4899', '#6B7280', '#FCD34D'
                ],
                hoverOffset: 8
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = safeNumber(context.raw);
                                const percent = ((value / totalExpense) * 100).toFixed(1);
                                return `${context.label}: €${value.toFixed(2)} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        };

        expenseChart = new Chart(ctx, config);
    }

    function initIncomeChart() {
        const ctx = document.getElementById('incomeChart');
        if (!ctx) return;

        if (incomeChart) incomeChart.destroy();

        const data = {
            labels: incomeLabels,
            datasets: [{
                label: 'Receitas',
                data: incomeValues.map(v => safeNumber(v)),
                backgroundColor: [
                    '#10B981', '#3B82F6', '#8B5CF6', '#EC4899',
                    '#EF4444', '#F59E0B', '#6B7280', '#FCD34D'
                ],
                hoverOffset: 8
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = safeNumber(context.raw);
                                const percent = ((value / totalIncome) * 100).toFixed(1);
                                return `${context.label}: €${value.toFixed(2)} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        };

        incomeChart = new Chart(ctx, config);
    }

    document.addEventListener("DOMContentLoaded", () => {
        initExpenseChart();
        initIncomeChart();
    });
</script>
