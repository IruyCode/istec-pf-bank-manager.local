@extends('bankmanager::app')

@section('content-component')

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Olá, {{ Auth::user()->name ?? 'Usuário' }}! 👋
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </p>
        </div>
        <form method="GET" action="{{ route('bank-manager.index') }}" class="flex items-center space-x-2">
            <select name="filter" id="filter" onchange="this.form.submit()"
                class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                <option value="total" {{ $accountTypeFilter === 'total' ? 'selected' : '' }}>Total (Pessoal + Empresarial)</option>
                <option value="personal" {{ $accountTypeFilter === 'personal' ? 'selected' : '' }}>Pessoal</option>
                <option value="business" {{ $accountTypeFilter === 'business' ? 'selected' : '' }}>Empresarial</option>
            </select>
        </form>
    </div>

    @include('bankmanager::dashboard.partials.balances')

    @include('bankmanager::dashboard.partials.spending-contexts')

    @include('bankmanager::dashboard.partials.active-accounts')

    @include('bankmanager::dashboard.partials.financial-summary')

    @include('bankmanager::dashboard.partials.charts')

    @include('bankmanager::fixed-expenses.index')

    @include('bankmanager::dashboard.partials.dataTables')
@endsection
