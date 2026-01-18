@extends('bankmanager::app')

@section('content-component')

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard Financeiro</h2>
        <form method="GET" action="{{ route('bank-manager.index') }}" class="flex items-center space-x-2">
            <select name="filter" id="filter" onchange="this.form.submit()"
                class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                <option value="total" {{ $accountTypeFilter === 'total' ? 'selected' : '' }}>Total (Pessoal +
                    Empresarial)</option>
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
