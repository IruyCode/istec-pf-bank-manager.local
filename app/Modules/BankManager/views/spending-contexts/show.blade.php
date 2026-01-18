@extends('bankmanager::app')

@section('content-component')

    <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
        <i class="fas fa-map-marked-alt text-blue-600"></i>
        {{ $spendingContext->name }}
    </h2>

    @php
        $transactions = $spendingContext->transactions;
        $spent = $transactions->sum('amount');
        $budget = $spendingContext->budget;
    @endphp

    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow mb-8 border border-gray-200 dark:border-gray-700">

        <p class="text-gray-600 dark:text-gray-300">
            {{ ucfirst($spendingContext->type) }} •
            {{ $spendingContext->start_date->format('d/m/Y') }} –
            {{ $spendingContext->end_date->format('d/m/Y') }}
        </p>

        <p class="mt-4 text-lg text-gray-800 dark:text-gray-200">
            Total gasto: <strong>€{{ number_format($spent, 2, ',', '.') }}</strong>
        </p>

        @if ($budget)
            <p class="text-gray-700 dark:text-gray-300">
                Orçamento: <strong>€{{ number_format($budget, 2, ',', '.') }}</strong>
            </p>

            @if ($spent > $budget)
                <p class="text-red-600 font-bold mt-2">⚠ Ultrapassou o orçamento!</p>
            @else
                <p class="text-green-600 mt-2">
                    Restante: €{{ number_format($budget - $spent, 2, ',', '.') }}
                </p>
            @endif
        @endif
    </div>

    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Transações</h3>

    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow border border-gray-200 dark:border-gray-700">
        <table class="min-w-full">
            <thead>
                <tr class="text-gray-500 dark:text-gray-300">
                    <th class="py-2 text-left">Data</th>
                    <th class="text-left">Categoria</th>
                    <th class="text-left">Descrição</th>
                    <th class="text-left">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $tx)
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">{{ $tx->created_at->format('d/m/Y') }}</td>
                        <td>{{ $tx->operationSubCategory->operationCategory->name ?? '-' }}</td>
                        <td>{{ $tx->note }}</td>
                        <td>€{{ number_format($tx->amount, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection
