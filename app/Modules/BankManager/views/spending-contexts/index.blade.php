@extends('bankmanager::app')

@section('content-component')

    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-globe-europe text-blue-600"></i>
            Contextos Financeiros
        </h2>

        <a href="{{ route('bank-manager.spending-contexts.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i>
            Criar Contexto
        </a>
    </div>

    @if ($contexts->isEmpty())
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow text-center text-gray-500 dark:text-gray-300">
            Nenhum contexto criado ainda.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

            @foreach ($contexts as $ctx)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 flex flex-col justify-between">

                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                            {{ $ctx->name }}
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <i class="fas fa-tag"></i>
                            {{ ucfirst($ctx->type) }}
                        </p>

                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                            🗓 {{ $ctx->start_date->format('d/m/Y') }}
                            → {{ $ctx->end_date->format('d/m/Y') }}
                        </p>

                        @if ($ctx->budget)
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                💰 Orçamento:
                                <strong>€{{ number_format($ctx->budget, 2, ',', '.') }}</strong>
                            </p>
                        @endif
                    </div>

                    <!-- BOTÕES -->
                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('bank-manager.spending-contexts.show', $ctx->id) }}"
                            class="text-blue-600 hover:underline">Ver</a>

                        <a href="{{ route('bank-manager.spending-contexts.edit', $ctx->id) }}"
                            class="text-yellow-600 hover:underline">Editar</a>

                        <form method="POST" action="{{ route('bank-manager.spending-contexts.destroy', $ctx->id) }}"
                            onsubmit="return confirm('Tem certeza que deseja remover este contexto?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Apagar</button>
                        </form>
                    </div>

                </div>
            @endforeach

        </div>
    @endif

@endsection
