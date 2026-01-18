   <div x-data="{ open: false }" class="bg-gray-800 p-4 rounded-lg shadow w-full">
       <!-- Botão para expandir -->
       <button @click="open = !open" class="w-full text-left text-white font-semibold">
           <span x-text="open ? '🔽 Ocultar Despesas Fixas' : '🔍 Consultar Despesas Fixas'"></span>
       </button>

       <!-- Conteúdo do filtro -->
       <div x-show="open" x-transition class="mt-4">

           <div class="container mx-auto px-4 py-8">
               <!-- Cabeçalho e Formulário de Adição -->
               <div class="mb-8">
                   <h1 class="text-2xl font-bold mb-4 text-white">Despesas Fixas Mensais</h1>

                   <!-- Formulário para nova despesa -->
                   <form method="POST" action="{{ route('bank-manager.fixed-expenses.createfixedExpense') }}"
                       class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
                       @csrf
                       <h2 class="text-xl font-semibold mb-4 text-white">Adicionar Nova Despesa Fixa</h2>

                       <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                           <div>
                               <label class="block text-sm font-medium text-gray-300 mb-1">Nome</label>
                               <input type="text" name="name" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                           </div>

                           <div>
                               <label class="block text-sm font-medium text-gray-300 mb-1">Valor (€)</label>
                               <input type="number" step="0.01" name="amount" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                               <label class="flex items-center mt-2 text-sm text-gray-400">
                                   <input type="checkbox" name="is_variable_amount" value="1"
                                       class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500 mr-2">
                                   Valor variável (ex: conta de luz)
                               </label>
                           </div>

                           <div>
                               <label class="block text-sm font-medium text-gray-300 mb-1">Dia do Vencimento</label>
                               <select name="due_day" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                   @for ($i = 1; $i <= 31; $i++)
                                       <option value="{{ $i }}">{{ $i }}</option>
                                   @endfor
                               </select>
                           </div>

                           <div class="flex items-end">
                               <button type="submit"
                                   class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                                   Adicionar
                               </button>
                           </div>
                       </div>
                   </form>
               </div>

               <!-- Formulário para marcar pagamentos -->
               <form method="POST" action="{{ route('bank-manager.fixed-expenses.markAsPaidFixedExpense') }}">
                   @csrf
                   @method('PUT')

                   <!-- Lista organizada por dia de vencimento -->
                   <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                       @for ($day = 1; $day <= 31; $day++)
                           @php
                               $expensesForDay = $fixedExpenses->where('due_day', $day);
                           @endphp

                           @if ($expensesForDay->count() > 0)
                               <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden border border-gray-700">
                                   <div class="bg-gray-900 px-4 py-3 border-b border-gray-700">
                                       <h2 class="font-semibold text-lg text-white">Dia {{ $day }}</h2>
                                   </div>

                                   <ul class="divide-y divide-gray-700">
                                       @foreach ($expensesForDay as $expense)
                                           @php
                                               $status = $expense->getStatusForMonth(now()->year, now()->month);
                                           @endphp

                                           <li class="px-4 py-3 hover:bg-gray-750 transition-colors">
                                               <div class="flex items-center justify-between">
                                                   <div class="flex items-center flex-1">
                                                       @if ($status !== 'paga')
                                                           <!-- Checkbox aparece para "em aberto" e "atrasada" -->
                                                           <input type="checkbox" name="expenses[]"
                                                               value="{{ $expense->id }}" id="expense-{{ $expense->id }}"
                                                               class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-gray-800 mr-3">
                                                       @endif

                                                       <label for="expense-{{ $expense->id }}" class="block cursor-pointer flex-1">
                                                           <span class="font-medium text-white">{{ $expense->name }}</span>
                                                           @if ($expense->is_variable_amount)
                                                               <span class="text-xs bg-yellow-600 text-white px-2 py-0.5 rounded ml-2">Variável</span>
                                                           @endif
                                                           <span class="text-gray-400 block text-sm">
                                                               Valor base: € {{ number_format($expense->amount, 2, ',', '.') }}
                                                           </span>
                                                           @if ($expense->is_variable_amount && $expense->payments()->count() > 0)
                                                               <span class="text-gray-500 block text-xs">
                                                                   Média últimos 6 meses: € {{ number_format($expense->getAverageAmount(), 2, ',', '.') }}
                                                               </span>
                                                           @endif
                                                       </label>
                                                   </div>

                                                   <div class="flex items-center space-x-3">
                                                       @if ($status === 'paga')
                                                           @php
                                                               $payment = $expense->payments()
                                                                   ->where('year', now()->year)
                                                                   ->where('month', now()->month)
                                                                   ->first();
                                                           @endphp
                                                           <span class="text-green-400 font-semibold text-sm">
                                                               ✅ Pago: € {{ number_format($payment->amount_paid, 2, ',', '.') }}
                                                           </span>
                                                       @elseif ($status === 'atrasada')
                                                           <span class="text-red-400 font-semibold text-sm">⚠️ Atrasado</span>
                                                       @endif

                                                       <!-- Botão excluir com JS -->
                                                       <button type="button" class="text-red-400 hover:text-red-300 transition-colors p-1 rounded hover:bg-gray-700"
                                                           onclick="deleteExpense('{{ route('bank-manager.fixed-expenses.destroyExpense', $expense->id) }}')">
                                                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                               viewBox="0 0 20 20" fill="currentColor">
                                                               <path fill-rule="evenodd"
                                                                   d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                   clip-rule="evenodd" />
                                                           </svg>
                                                       </button>
                                                   </div>
                                               </div>

                                               <!-- Campo para editar valor (apenas se for variável e não paga) -->
                                               @if ($expense->is_variable_amount)
                                                   <div class="mt-2 ml-7 flex items-center gap-2">
                                                       @if ($status !== 'paga')
                                                           <div class="flex-1">
                                                               <label class="block text-xs text-gray-400 mb-1">Valor deste mês (€):</label>
                                                               <input type="number" step="0.01" 
                                                                   name="amounts[{{ $expense->id }}]" 
                                                                   value="{{ $expense->getSuggestedAmount() }}"
                                                                   placeholder="{{ $expense->getSuggestedAmount() }}"
                                                                   class="w-48 px-2 py-1 text-sm bg-gray-700 border border-gray-600 rounded-md text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                           </div>
                                                       @endif
                                                       
                                                       @if ($expense->payments->count() > 0)
                                                           <button type="button" 
                                                               onclick="openHistoryModal({{ $expense->id }})"
                                                               class="px-3 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600 border border-gray-600 {{ $status !== 'paga' ? 'mt-5' : '' }}">
                                                               📊 Ver Histórico
                                                           </button>
                                                       @endif
                                                   </div>
                                               @endif
                                           </li>
                                       @endforeach
                                   </ul>
                               </div>
                           @endif
                       @endfor
                   </div>

                   <div class="mt-6 bg-gray-800 p-4 rounded-lg border border-gray-700">
                       <label class="block text-sm font-medium text-gray-300 mb-2">
                           Selecionar Conta para débito
                       </label>

                       <select name="account_balance_id" required
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                           @foreach ($accountBalance as $account)
                               <option value="{{ $account->id }}">
                                   {{ $account->account_name }} ({{ $account->bank_name }}) –
                                   Saldo: €{{ number_format($account->current_balance, 2, ',', '.') }}
                               </option>
                           @endforeach
                       </select>

                       <!-- Botão para marcar como pago -->
                       <button type="submit"
                           class="mt-4 w-full bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 transition-colors font-semibold focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                           💰 Marcar selecionados como pagos
                       </button>
                   </div>
               </form>

               <!-- Script de exclusão -->
               <script>
                   // Dados de histórico de pagamentos (gerados pelo Laravel)
                   const expenseHistories = {
                       @foreach($fixedExpenses as $expense)
                           {{ $expense->id }}: {
                               name: "{{ $expense->name }}",
                               payments: [
                                   @foreach($expense->payments as $payment)
                                       {
                                           year: {{ $payment->year }},
                                           month: {{ $payment->month }},
                                           amount: {{ $payment->amount_paid }},
                                           monthName: "{{ \Carbon\Carbon::create($payment->year, $payment->month)->locale('pt_BR')->isoFormat('MMMM YYYY') }}"
                                       },
                                   @endforeach
                               ]
                           },
                       @endforeach
                   };

                   function deleteExpense(url) {
                       if (!confirm('Tem certeza que deseja excluir esta despesa fixa?')) return;

                       const form = document.createElement('form');
                       form.method = 'POST';
                       form.action = url;

                       const csrf = document.createElement('input');
                       csrf.type = 'hidden';
                       csrf.name = '_token';
                       csrf.value = '{{ csrf_token() }}';
                       form.appendChild(csrf);

                       const method = document.createElement('input');
                       method.type = 'hidden';
                       method.name = '_method';
                       method.value = 'DELETE';
                       form.appendChild(method);

                       document.body.appendChild(form);
                       form.submit();
                   }

                   function openHistoryModal(expenseId) {
                       const history = expenseHistories[expenseId];
                       
                       if (!history || history.payments.length === 0) {
                           alert('Nenhum histórico disponível');
                           return;
                       }

                       const payments = history.payments;
                       const total = payments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
                       const average = (total / payments.length).toFixed(2);

                       let tableRows = '';
                       payments.forEach((payment, index) => {
                           tableRows += `
                               <tr class="${index % 2 === 0 ? 'bg-gray-800' : 'bg-gray-750'}">
                                   <td class="px-4 py-2 text-gray-300">${payment.monthName}</td>
                                   <td class="px-4 py-2 text-right text-white font-medium">€ ${parseFloat(payment.amount).toFixed(2).replace('.', ',')}</td>
                               </tr>
                           `;
                       });

                       const modalHTML = `
                           <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" onclick="closeHistoryModal(event)">
                               <div class="bg-gray-900 rounded-lg shadow-2xl max-w-md w-full mx-4 border border-gray-700" onclick="event.stopPropagation()">
                                   <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 rounded-t-lg">
                                       <div class="flex items-center justify-between">
                                           <h3 class="text-xl font-bold text-white">📊 Histórico: ${history.name}</h3>
                                           <button onclick="closeHistoryModal()" class="text-white hover:text-gray-300 text-2xl leading-none">&times;</button>
                                       </div>
                                   </div>
                                   
                                   <div class="p-6">
                                       <div class="mb-4 grid grid-cols-2 gap-4">
                                           <div class="bg-gray-800 p-3 rounded-lg border border-gray-700">
                                               <div class="text-gray-400 text-xs mb-1">Média</div>
                                               <div class="text-2xl font-bold text-indigo-400">€ ${average.replace('.', ',')}</div>
                                           </div>
                                           <div class="bg-gray-800 p-3 rounded-lg border border-gray-700">
                                               <div class="text-gray-400 text-xs mb-1">Registros</div>
                                               <div class="text-2xl font-bold text-purple-400">${payments.length}</div>
                                           </div>
                                       </div>

                                       <div class="max-h-96 overflow-y-auto">
                                           <table class="w-full">
                                               <thead class="bg-gray-800 sticky top-0">
                                                   <tr>
                                                       <th class="px-4 py-2 text-left text-gray-300 text-sm font-semibold">Mês/Ano</th>
                                                       <th class="px-4 py-2 text-right text-gray-300 text-sm font-semibold">Valor Pago</th>
                                                   </tr>
                                               </thead>
                                               <tbody>
                                                   ${tableRows}
                                               </tbody>
                                           </table>
                                       </div>
                                   </div>
                                   
                                   <div class="px-6 py-4 bg-gray-800 rounded-b-lg border-t border-gray-700">
                                       <button onclick="closeHistoryModal()" 
                                           class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded transition-colors">
                                           Fechar
                                       </button>
                                   </div>
                               </div>
                           </div>
                       `;

                       document.body.insertAdjacentHTML('beforeend', modalHTML);
                   }

                   function closeHistoryModal(event) {
                       if (!event || event.target.id === 'historyModal' || event.type !== 'click') {
                           const modal = document.getElementById('historyModal');
                           if (modal) {
                               modal.remove();
                           }
                       }
                   }

                   // Fechar modal com ESC
                   document.addEventListener('keydown', function(e) {
                       if (e.key === 'Escape') {
                           closeHistoryModal();
                       }
                   });
               </script>
           </div>
       </div>
   </div>
