<!-- Detalhes das Parcelas (Expandível) -->
<div x-show="isExpanded({{ $debt->id }})" x-collapse.duration.300ms class="bg-gray-50 dark:bg-gray-700/30 px-6 py-4">
    <!-- Próxima Parcela -->
    @if ($nextInstallment)
        <div
            class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-900/30 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                            PRÓXIMA PARCELA
                        </span>
                        @if (\Carbon\Carbon::parse($nextInstallment->due_date)->isPast())
                            <span
                                class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                ATRASADA
                            </span>
                        @endif
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ \Carbon\Carbon::parse($nextInstallment->due_date)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </h4>
                    <div class="mt-1 flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Parcela #{{ $nextInstallment->installment_number }}
                        </span>
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            € {{ number_format($nextInstallment->amount, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Info: Pagamento via Despesas Fixas -->
                <div class="text-center sm:text-right">
                    @if($debt->status === 'active')
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2">
                            <p class="text-xs text-blue-700 dark:text-blue-300 font-medium mb-1">
                                💡 Pagamento Automático
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Esta dívida está ativa. Pague através das <a href="{{ route('bank-manager.index') }}" class="text-blue-600 dark:text-blue-400 underline hover:text-blue-700">Despesas Fixas</a>.
                            </p>
                        </div>
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg px-4 py-2">
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 font-medium mb-1">
                                ⚠️ Dívida Inativa
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Ative esta dívida para gerar despesas fixas automaticamente.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Botão de pagamento direto removido --}}
                {{-- O pagamento agora é feito apenas via Despesas Fixas quando a dívida está ativa --}}
            </div>
        </div>
    @endif


    <!-- Lista Resumida de Parcelas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4">
        <h4 class="text-md font-medium text-gray-800 dark:text-white mb-3">
            Próximas parcelas ({{ $pendingInstallments->count() }} restantes)
        </h4>

        <div class="space-y-2">
            @foreach ($pendingInstallments->take(5) as $installment)
                <div
                    class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                            #{{ $installment->installment_number }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}
                        </span>
                    </div>
                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                        € {{ number_format($installment->amount, 2) }}
                    </span>
                </div>
            @endforeach

            @if ($pendingInstallments->count() > 5)
                <div class="pt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                    +{{ $pendingInstallments->count() - 5 }} parcelas não exibidas
                </div>
            @endif
        </div>
    </div>
</div>
