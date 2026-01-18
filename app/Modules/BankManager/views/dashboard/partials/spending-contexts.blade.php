   @if ($activeContext || $upcomingContexts->isNotEmpty())
       <div x-data="{ openContextCard: false }"
           class="mb-10 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">



           <!-- CABEÇALHO DO CARD (CLICK PARA EXPANDIR) -->
           <button @click="openContextCard = !openContextCard"
               class="w-full px-6 py-4 flex justify-between items-center text-left">

               <div class="flex items-center gap-3">
                   <i class="fas fa-globe-europe text-blue-600 dark:text-blue-400 text-2xl"></i>
                   <h2 class="text-xl font-extrabold text-gray-900 dark:text-gray-200">
                       Contextos Financeiros
                   </h2>
               </div>

               <span class="text-gray-600 dark:text-gray-300 text-lg transform transition"
                   :class="openContextCard ? 'rotate-180' : 'rotate-0'">
                   ⌄
               </span>
           </button>

           <!-- CONTEÚDO EXPANSÍVEL -->
           <div x-show="openContextCard" x-transition class="px-6 pb-6 space-y-8">

               <a href="{{ route('bank-manager.spending-contexts.index') }}"
                   class="inline-flex items-center gap-3 px-5 py-3 rounded-xl font-semibold
                        bg-gradient-to-r from-blue-600 to-blue-500
                        text-white shadow-lg shadow-blue-600/30
                        hover:shadow-blue-700/40 hover:scale-[1.02]
                        transition-all duration-300
                        dark:from-blue-500 dark:to-blue-400">

                   <i class="fas fa-plus-circle text-xl animate-pulse"></i>

                   <span class="text-sm tracking-wide">
                       Criar Novo Contexto Financeiro
                   </span>
               </a>


               {{-- 🔵 CONTEXTO ATIVO --}}
               @if ($activeContext)
                   <div
                       class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/5
                border border-blue-300 dark:border-blue-700 rounded-xl p-5 shadow-inner space-y-5">

                       <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                           <h3 class="text-lg font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                               <i class="fas fa-map-marked-alt text-blue-600 dark:text-blue-400"></i>
                               Contexto ativo: {{ $activeContext->name }}
                           </h3>

                           <a href="{{ route('bank-manager.spending-contexts.show', $activeContext->id) }}"
                               class="px-4 py-1.5 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 
                        dark:bg-blue-500 dark:hover:bg-blue-600 shadow transition">
                               Ver detalhes
                           </a>
                       </div>

                       <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-gray-700 dark:text-gray-200">

                           <div class="bg-white/80 dark:bg-gray-900/30 backdrop-blur p-4 rounded-lg shadow-sm">
                               <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Período</p>
                               <p class="font-semibold mt-1">
                                   {{ $activeContext->start_date->format('d/m/Y') }} →
                                   {{ $activeContext->end_date->format('d/m/Y') }}
                               </p>
                           </div>

                           <div class="bg-white/80 dark:bg-gray-900/30 backdrop-blur p-4 rounded-lg shadow-sm">
                               <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Gasto até
                                   agora</p>
                               <p class="font-semibold text-blue-700 dark:text-blue-300 mt-1">
                                   € {{ number_format($contextTotalSpent, 2, ',', '.') }}
                               </p>
                           </div>

                           @if ($contextBudget)
                               <div class="bg-white/80 dark:bg-gray-900/30 backdrop-blur p-4 rounded-lg shadow-sm">
                                   <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Orçamento
                                   </p>
                                   <p class="font-semibold mt-1">
                                       € {{ number_format($contextBudget, 2, ',', '.') }}
                                   </p>
                               </div>
                           @endif

                       </div>

                       @if ($contextBudget)
                           <div class="space-y-1">
                               <div class="w-full h-3 bg-gray-300 dark:bg-gray-700 rounded-full overflow-hidden">
                                   <div class="h-3 transition-all duration-500
                                @if ($contextPercentUsed < 80) bg-blue-600
                                @elseif ($contextPercentUsed < 100) bg-yellow-500
                                @else bg-red-600 @endif"
                                       style="width: {{ min($contextPercentUsed, 100) }}%;">
                                   </div>
                               </div>
                               <p class="text-xs text-gray-600 dark:text-gray-400">
                                   {{ number_format($contextPercentUsed, 1) }}% do orçamento utilizado
                               </p>
                           </div>
                       @endif

                       @if ($contextTransactions->count())
                           <div class="pt-2 border-t border-gray-300/50 dark:border-gray-700/50">
                               <p class="text-sm font-semibold mb-2 text-gray-800 dark:text-gray-200">
                                   Transações vinculadas ({{ $contextTransactions->count() }}):
                               </p>

                               <ul class="space-y-1 text-xs">
                                   @foreach ($contextTransactions as $t)
                                       <li class="text-gray-700 dark:text-gray-400 flex items-center gap-2">
                                           <i class="fas fa-circle text-[6px]"></i>
                                           {{ $t->operationSubCategory->name }} —
                                           € {{ number_format($t->amount, 2, ',', '.') }}
                                       </li>
                                   @endforeach
                               </ul>
                           </div>
                       @endif

                   </div>
               @endif


               {{-- 🟣 PRÓXIMOS CONTEXTOS --}}
               @if ($upcomingContexts->isNotEmpty())
                   <div
                       class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/5
                 border border-purple-300 dark:border-purple-700 rounded-xl p-5 shadow-inner">

                       <h3 class="text-lg font-bold text-purple-900 dark:text-purple-200 flex items-center gap-2 mb-4">
                           <i class="fas fa-calendar-week text-purple-600 dark:text-purple-400"></i>
                           Próximos contextos
                       </h3>

                       <ul class="space-y-3">
                           @foreach ($upcomingContexts as $ctx)
                               <li
                                   class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow flex justify-between items-center">

                                   <div>
                                       <p class="font-semibold text-gray-900 dark:text-gray-200">{{ $ctx->name }}
                                       </p>
                                       <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                           {{ $ctx->start_date->format('d/m/Y') }} →
                                           {{ $ctx->end_date->format('d/m/Y') }}
                                       </p>
                                   </div>

                                   <a href="{{ route('bank-manager.spending-contexts.show', $ctx->id) }}"
                                       class="px-3 py-1.5 text-xs rounded-md bg-purple-600 text-white hover:bg-purple-700 
                                dark:bg-purple-500 dark:hover:bg-purple-600 shadow transition">
                                       Ver
                                   </a>

                               </li>
                           @endforeach
                       </ul>

                   </div>
               @endif


           </div>
       </div>
   @endif
