@extends('bankmanager::app')

@section('content-component')
<div class="min-h-screen bg-gray-950">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Gestão de Utilizadores</h1>
            <p class="text-gray-400">Visualize informações sobre os utilizadores e suas contas</p>
        </div>

        <!-- Cards de Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total de Utilizadores</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ $users->count() }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-500 opacity-50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total de Contas</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ $users->sum('account_count') }}</p>
                    </div>
                    <svg class="w-12 h-12 text-green-500 opacity-50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3M12 2v20" />
                    </svg>
                </div>
            </div>

            <div class="bg-gray-900 rounded-lg p-6 border border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Média de Contas por Utilizador</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ round($users->count() > 0 ? $users->sum('account_count') / $users->count() : 0, 2) }}</p>
                    </div>
                    <svg class="w-12 h-12 text-purple-500 opacity-50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tabela de Utilizadores -->
        <div class="bg-gray-900 rounded-lg border border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-800 bg-gray-800/50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Nome</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Email</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Tipo de Utilizador</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-300">Nº de Contas</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Último Acesso</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Data de Criação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-800/50 transition duration-200">
                                <td class="px-6 py-4 text-sm text-white font-medium">
                                    {{ $user['name'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $user['email'] }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($user['type_user_id'] === 1)
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-orange-300 bg-orange-900/30 rounded-full border border-orange-700">
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-green-300 bg-green-900/30 rounded-full border border-green-700">
                                            Utilizador
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-3 py-1 text-sm font-semibold text-blue-300 bg-blue-900/30 rounded-lg border border-blue-700">
                                        {{ $user['account_count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    @if($user['last_login'])
                                        <span title="{{ $user['last_login']->format('d/m/Y H:i:s') }}">
                                            {{ $user['last_login']->format('d/m/Y H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-500">Nunca</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $user['created_at']->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    Nenhum utilizador encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
