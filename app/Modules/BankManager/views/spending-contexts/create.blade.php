@extends('bankmanager::app')

@section('content-component')
    <div
        class="max-w-3xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-gray-200 dark:border-gray-700">

        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-200 mb-6 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-600"></i>
            Criar Novo Contexto
        </h2>

        <form method="POST" action="{{ route('bank-manager.spending-contexts.store') }}" class="space-y-6">
            @csrf

            <!-- Nome -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Nome do Contexto</label>
                <input type="text" name="name" required
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                <select name="type" required
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                    <option value="trip">Viagem</option>
                    <option value="vacation">Férias</option>
                    <option value="event">Evento</option>
                    <option value="other">Outro</option>
                </select>
            </div>

            <!-- Datas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Data de Início</label>
                    <input type="date" name="start_date" required
                        class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Data de Fim</label>
                    <input type="date" name="end_date" required
                        class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                </div>
            </div>

            <!-- Orçamento -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Orçamento (opcional)</label>
                <input type="number" step="0.01" name="budget"
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
            </div>

            <!-- Botão -->
            <div class="flex justify-end">
                <button class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    Criar Contexto
                </button>
            </div>

        </form>

    </div>
@endsection
