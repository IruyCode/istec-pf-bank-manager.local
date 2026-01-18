@extends('layout.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        
        {{-- Header com saudação --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Olá, {{ Auth::user()->name ?? 'Usuário' }}! 👋
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    {{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sair
                </button>
            </form>
        </div>

        {{-- Módulos --}}
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">🚀 Seus Módulos</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <!-- BANK MANAGER -->
                <a href="{{ route('bank-manager.index') }}"
                   class="group bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg group-hover:scale-110 transition">
                            <span class="text-3xl">💰</span>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded-full">
                            €{{ number_format($totalBalance ?? 0, 0) }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Bank Manager</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Controle suas finanças, investimentos e despesas
                    </p>
                    <div class="flex items-center text-blue-600 dark:text-blue-400 text-sm font-medium">
                        Acessar módulo
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        {{-- Central de Notificações --}}
        @if(isset($notifications) && $notificationCounts['total'] > 0)
        <div id="notifications" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden scroll-mt-24">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">🔔 Central de Notificações</h2>
                        <p class="text-white/80 text-sm">{{ $notificationCounts['total'] }} notificação(ões) ativa(s)</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('core.notifications.index') }}" 
                       class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg text-sm font-medium transition">
                        Ver todas
                    </a>
                    <form method="POST" action="{{ route('core.notifications.check-all') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 bg-white text-orange-600 hover:bg-orange-50 rounded-lg text-sm font-medium transition">
                            Marcar todas como lidas
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6 space-y-6">
                @foreach($notifications as $module => $moduleNotifications)
                    <div class="space-y-3">
                        {{-- Cabeçalho do Módulo --}}
                        <div class="flex items-center gap-2 mb-3">
                            @php
                                $moduleConfig = [
                                    'bank-manager' => ['name' => 'Bank Manager', 'icon' => '💰', 'color' => 'blue'],
                                ];
                                $config = $moduleConfig[$module] ?? ['name' => ucfirst($module), 'icon' => '🔔', 'color' => 'gray'];
                            @endphp
                            <span class="text-2xl">{{ $config['icon'] }}</span>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $config['name'] }}</h3>
                            <span class="px-2 py-1 bg-{{ $config['color'] }}-100 dark:bg-{{ $config['color'] }}-900/30 text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300 text-xs font-semibold rounded-full">
                                {{ $moduleNotifications->count() }}
                            </span>
                        </div>

                        {{-- Lista de Notificações do Módulo --}}
                        <div class="space-y-2">
                            @foreach($moduleNotifications->take(5) as $notification)
                                <div class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 hover:border-{{ $config['color'] }}-300 dark:hover:border-{{ $config['color'] }}-700 transition group">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4 mb-2">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $notification->title }}
                                                @if($notification->type === 'habit_reminder' && str_contains($notification->title, 'URGENTE'))
                                                    <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-bold rounded">
                                                        URGENTE
                                                    </span>
                                                @endif
                                            </h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                {{ $notification->triggered_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $notification->message }}</p>
                                        
                                        <div class="flex items-center gap-2">
                                            @if($notification->url)
                                                <a href="{{ $notification->url }}" 
                                                   class="inline-flex items-center gap-1 text-xs font-medium text-{{ $config['color'] }}-600 dark:text-{{ $config['color'] }}-400 hover:text-{{ $config['color'] }}-700 dark:hover:text-{{ $config['color'] }}-300">
                                                    Ver detalhes
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Ações --}}
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                        <form method="POST" action="{{ route('core.notifications.check', $notification->id) }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition"
                                                    title="Marcar como lida">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('core.notifications.ignore', $notification->id) }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition"
                                                    title="Ignorar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
@endsection
