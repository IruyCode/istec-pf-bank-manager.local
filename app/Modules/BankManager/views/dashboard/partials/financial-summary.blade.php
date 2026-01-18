<div x-data="modalActions()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

        <!-- === CARD: RESUMO FINANCEIRO === -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-600"></i>
                Resumo Financeiro ({{ now()->format('F Y') }})
            </h3>

            <!-- GRID INTERNO DO RESUMO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Saldo Consolidado -->
                <div class="p-5 rounded-xl border dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                        Saldo Consolidado ({{ ucfirst($accountTypeFilter) }})
                    </p>
                    <h1
                        class="text-3xl font-bold
                    @if ($totalBalance > 0) text-green-600
                    @elseif ($totalBalance < 0) text-red-600
                    @else text-yellow-500 @endif">
                        € {{ number_format($totalBalance, 2, ',', '.') }}
                    </h1>
                </div>

                <!-- Receitas -->
                <div class="p-5 rounded-xl bg-green-50 dark:bg-green-900/20">
                    <p class="text-sm text-green-800 dark:text-green-200 mb-1">Receitas</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-300">
                        € {{ number_format($totalIncome, 2, ',', '.') }}
                    </p>
                </div>

                <!-- Despesas -->
                <div class="p-5 rounded-xl bg-red-50 dark:bg-red-900/20">
                    <p class="text-sm text-red-800 dark:text-red-200 mb-1">Despesas</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-300">
                        € {{ number_format($totalExpense, 2, ',', '.') }}
                    </p>
                </div>

            </div>

            <!-- FIXAS E MÉDIA -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                <div
                    class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border dark:border-gray-700">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        <span class="text-gray-700 dark:text-gray-300">Despesas Fixas (Mês)</span>
                    </span>
                    <span class="font-semibold text-red-500">
                        € {{ number_format($fixedExpenseTotal, 2, ',', '.') }}
                    </span>
                </div>

                <div
                    class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border dark:border-gray-700">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        <span class="text-gray-700 dark:text-gray-300">Média de Gasto Mensal</span>
                    </span>
                    <span class="font-semibold">
                        € {{ number_format($averageMonthlyExpense, 2, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>

        <!-- === CARD: AÇÕES RÁPIDAS === -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 flex flex-col justify-between">

            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-yellow-500"></i>
                Ações Rápidas
            </h3>

            <div class="flex flex-col gap-3">

                <!-- Configurações -->
                <a href="{{ route('bank-manager.settings') }}"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg
                      bg-purple-600 hover:bg-purple-700 text-white font-semibold shadow-sm
                      transition-all duration-200">
                    <i class="fas fa-cog"></i>
                    Configurações
                </a>

                <!-- Criar Categoria -->
                <button @click="showCategoriaModal = true"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg
                           bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm
                           transition-all duration-200">
                    <i class="fas fa-plus"></i>
                    Criar Categoria
                </button>

                <!-- Criar Subcategoria -->
                <button @click="showSubCategoriaModal = true"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg
                           bg-yellow-500 hover:bg-yellow-600 text-white font-semibold shadow-sm
                           transition-all duration-200">
                    <i class="fas fa-plus"></i>
                    Criar Subcategoria
                </button>

                <!-- Criar Transação -->
                <button @click="showTransacaoModal = true"
                    class="flex items-center justify-center gap-2 py-3 px-4 rounded-lg
                           bg-green-600 hover:bg-green-700 text-white font-semibold shadow-sm
                           transition-all duration-200">
                    <i class="fas fa-plus"></i>
                    Adicionar Transação
                </button>

            </div>
        </div>
        <!-- === Modais === -->
        <!-- MODAL — Criar Categoria -->
        <div x-show="showCategoriaModal" x-transition x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
            @click.self="showCategoriaModal = false">

            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Criar Nova Categoria</h3>
                    <button @click="showCategoriaModal = false" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('bank-manager.categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Tipo</label>
                        <select name="operation_type_id" class="w-full bg-gray-700 text-white rounded px-3 py-2"
                            required>
                            <option value="">Selecionar Tipo</option>
                            @foreach ($operationTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->operation_type === 'income' ? 'Receita' : 'Despesa' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Nome da Categoria</label>
                        <input type="text" name="name" class="w-full bg-gray-700 text-white rounded px-3 py-2"
                            required>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCategoriaModal = false"
                            class="px-4 py-2 text-gray-300 hover:text-white">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL — Criar Subcategoria -->
        <div x-show="showSubCategoriaModal" x-transition x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
            @click.self="showSubCategoriaModal = false">

            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4" x-data="subCategoryForm()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Criar Nova Subcategoria</h3>
                    <button @click="showSubCategoriaModal = false" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('bank-manager.subcategories.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Tipo</label>
                        <select x-model="selectedType" name="operation_type_id"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required>
                            <option value="">Selecionar Tipo</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id"
                                    x-text="type.operation_type === 'income' ? 'Receita' : 'Despesa'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Categoria Pai</label>
                        <select x-model="selectedCategory" name="operation_category_id"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required
                            :disabled="!selectedType">
                            <option value="">
                                <template x-if="!selectedType">Selecione um tipo primeiro</template>
                                <template x-if="selectedType">Selecionar Categoria</template>
                            </option>
                            <template x-for="cat in filteredCategories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        <p x-show="selectedType && filteredCategories.length === 0"
                            class="text-xs text-yellow-400 mt-1">
                            Nenhuma categoria disponível para este tipo. Crie uma categoria primeiro.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Nome da Subcategoria</label>
                        <input type="text" name="name" class="w-full bg-gray-700 text-white rounded px-3 py-2"
                            required>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showSubCategoriaModal = false"
                            class="px-4 py-2 text-gray-300 hover:text-white">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL — Criar Transação -->
        <div x-show="showTransacaoModal" x-transition x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
            @click.self="showTransacaoModal = false">

            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md mx-4" x-data="bankForm()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">Adicionar Nova Transação</h3>
                    <button @click="showTransacaoModal = false" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if ($activeContext)
                <div class="mb-4 p-4 rounded-lg border border-blue-700 bg-blue-900/30 text-blue-200">
                    <div class="flex justify-between items-center">
                        <p class="font-semibold text-lg flex items-center gap-2">
                            <i class="fas fa-suitcase-rolling"></i>
                            {{ $activeContext->name }}
                        </p>
                    </div>

                    <p class="text-sm mt-1">
                        <strong>Período:</strong>
                        {{ \Carbon\Carbon::parse($activeContext->start_date)->format('d/m/Y') }}
                        →
                        {{ \Carbon\Carbon::parse($activeContext->end_date)->format('d/m/Y') }}
                    </p>

                    <p class="text-sm mt-1">
                        <strong>Gasto até agora:</strong>
                        €{{ number_format($contextTotalSpent, 2, ',', '.') }}
                    </p>

                    @if ($contextBudget)
                    <p class="text-sm mt-1">
                        <strong>Orçamento:</strong>
                        €{{ number_format($contextBudget, 2, ',', '.') }}
                    </p>

                    <div class="mt-2">
                        <div class="w-full bg-gray-600 h-2 rounded-full">
                            <div class="h-2 rounded-full bg-blue-500"
                                style="width:  min(contextPercentUsed, 100) ">
                            </div>
                        </div>
                        <p class="text-xs text-blue-300 mt-1">
                            {{ number_format($contextPercentUsed, 1) }}% do orçamento utilizado
                        </p>
                    </div>
                    @endif

                    <div class="mt-3 text-xs text-blue-300">
                        Todas as transações criadas agora serão associadas automaticamente a este evento.
                    </div>
                </div>
                @endif


                <form action="{{ route('bank-manager.transactions.store') }}" method="POST">
                    @csrf

                    <!-- Conta -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Conta</label>
                        <select x-model="selectedAccount" name="account_balance_id"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required>
                            <option value="">Selecionar Conta</option>
                            <template x-for="acc in accounts" :key="acc.id">
                                <option :value="acc.id" x-text="acc.account_name + ' (' + acc.bank_name + ')'">
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Tipo</label>
                        <select x-model="selectedType" name="operation_type_id"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required>
                            <option value="">Tipo</option>
                            <template x-for="type in types" :key="type.id">
                                <option :value="type.id" x-text="translateType(type.operation_type)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Categoria -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Categoria</label>
                        <select x-model="selectedCategory" class="w-full bg-gray-700 text-white rounded px-3 py-2"
                            required :disabled="!selectedType">
                            <option value="">
                                <template x-if="!selectedType">Selecione um tipo primeiro</template>
                                <template x-if="selectedType">Selecionar Categoria</template>
                            </option>
                            <template x-for="category in filteredCategories" :key="category.id">
                                <option :value="category.id" x-text="category.name"></option>
                            </template>
                        </select>
                        <p x-show="selectedType && filteredCategories.length === 0"
                            class="text-xs text-yellow-400 mt-1">
                            Nenhuma categoria disponível para este tipo.
                        </p>
                    </div>

                    <!-- Subcategoria -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Subcategoria</label>
                        <select x-model="selectedSubCategory" name="operation_sub_category_id"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required
                            :disabled="!selectedCategory">
                            <option value="">
                                <template x-if="!selectedCategory">Selecione uma categoria primeiro</template>
                                <template x-if="selectedCategory">Selecionar Subcategoria</template>
                            </option>
                            <template x-for="sub in filteredSubCategories" :key="sub.id">
                                <option :value="sub.id" x-text="sub.name"></option>
                            </template>
                        </select>
                        <p x-show="selectedCategory && filteredSubCategories.length === 0"
                            class="text-xs text-yellow-400 mt-1">
                            Nenhuma subcategoria disponível para esta categoria.
                        </p>
                    </div>

                    <!-- Data da transação -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Data da transação</label>
                        <input type="date" name="performed_at"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2">
                        <p class="text-xs text-gray-400 mt-1">
                            Se não informar, será usada a data de hoje.
                        </p>
                    </div>

                    <!-- Valor -->
                    <div class="mb-4">
                        <label class="block text-gray-300 mb-2">Valor</label>
                        <input type="number" step="0.01" name="amount" x-model="amount"
                            class="w-full bg-gray-700 text-white rounded px-3 py-2" required>
                    </div>



                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showTransacaoModal = false"
                            class="px-4 py-2 text-gray-300 hover:text-white">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Dados do backend
    window.bankManagerData = <?php echo json_encode([
                                    'operationTypes' => $operationTypes,
                                    'operationCategories' => $operationCategories,
                                    'operationSubCategories' => $operationSubCategories,
                                    'accounts' => $accounts
                                ]); ?>;

    function modalActions() {
        return {
            showCategoriaModal: false,
            showSubCategoriaModal: false,
            showTransacaoModal: false,

            novaCategoria: {
                nome: '',
            }
        }
    }

    function bankForm() {
        return {
            types: window.bankManagerData.operationTypes,
            categories: window.bankManagerData.operationCategories,
            subcategories: window.bankManagerData.operationSubCategories,
            accounts: window.bankManagerData.accounts,

            selectedType: "",
            selectedCategory: "",
            selectedSubCategory: "",
            selectedAccount: "",
            amount: "",

            get filteredCategories() {
                if (!this.selectedType) return [];
                return this.categories.filter(c =>
                    Number(c.operation_type_id) === Number(this.selectedType)
                );
            },

            get filteredSubCategories() {
                if (!this.selectedCategory) return [];
                return this.subcategories.filter(s =>
                    Number(s.operation_category_id) === Number(this.selectedCategory) &&
                    Number(s.operation_type_id) === Number(this.selectedType)
                );
            },

            translateType(type) {
                return type === "income" ?
                    "Receita" :
                    type === "expense" ?
                    "Despesa" :
                    type;
            }
        };
    }

    function subCategoryForm() {
        return {
            types: window.bankManagerData.operationTypes,
            categories: window.bankManagerData.operationCategories,
            selectedType: "",
            selectedCategory: "",

            get filteredCategories() {
                if (!this.selectedType) return [];
                return this.categories.filter(c =>
                    Number(c.operation_type_id) === Number(this.selectedType)
                );
            }
        };
    }
</script>