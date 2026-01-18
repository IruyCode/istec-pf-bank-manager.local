@extends('bankmanager::app')

@section('content-component')
    <div
        class="max-w-3xl mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-gray-200 dark:border-gray-700">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-200 flex items-center gap-2">
                <i class="fas fa-edit text-yellow-500"></i>
                Editar Contexto
            </h2>

            <a href="{{ route('bank-manager.spending-contexts.show', $context->id) }}"
                class="px-4 py-2 rounded-lg text-sm bg-gray-600 text-white hover:bg-gray-700 transition">
                Voltar
            </a>
        </div>

        <form method="POST" action="{{ route('bank-manager.spending-contexts.update', $context->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nome -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Nome</label>
                <input type="text" name="name" value="{{ old('name', $context->name) }}"
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
            </div>

            <!-- Datas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Data de Início</label>
                    <input type="date" name="start_date" value="{{ $context->start_date->toDateString() }}"
                        class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                </div>

                <div>
                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Data de Fim</label>
                    <input type="date" name="end_date" value="{{ $context->end_date->toDateString() }}"
                        class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                </div>

            </div>

            <!-- Orçamento -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Orçamento</label>
                <input type="number" step="0.01" name="budget" value="{{ $context->budget }}"
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
            </div>

            <!-- Ativo -->
            <div>
                <label class="block text-gray-700 dark:text-gray-300 mb-1">Ativo?</label>
                <select name="is_active"
                    class="w-full bg-gray-100 dark:bg-gray-700 rounded-lg px-4 py-2 border dark:border-gray-600 text-gray-900 dark:text-gray-200">
                    <option value="1" {{ $context->is_active ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ !$context->is_active ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('bank-manager.spending-contexts.show', $context->id) }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    Cancelar
                </a>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md">
                    Atualizar
                </button>
            </div>

        </form>

    </div>
@endsection
