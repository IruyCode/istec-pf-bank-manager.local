@extends('layout.app')

@section('content')
    <div class="max-w-6xl mx-auto mt-6 px-4">

        <!-- HEADER -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">📬 Notificações</h2>
            
            <form method="POST" action="{{ route('core.notifications.check-all') }}">
                @csrf
                <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition">
                    ✓ Marcar todas como lidas
                </button>
            </form>
        </div>

        <!-- ESTATÍSTICAS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</p>
                <p class="text-xs text-blue-800 dark:text-blue-200">Total</p>
            </div>
            <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['active'] }}</p>
                <p class="text-xs text-yellow-800 dark:text-yellow-200">Ativas</p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['checked'] }}</p>
                <p class="text-xs text-green-800 dark:text-green-200">Lidas</p>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $stats['ignored'] }}</p>
                <p class="text-xs text-gray-700 dark:text-gray-400">Ignoradas</p>
            </div>
        </div>

        <!-- FILTROS -->
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Todas</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Ativas</option>
                        <option value="checked" {{ $status === 'checked' ? 'selected' : '' }}>Lidas</option>
                        <option value="ignored" {{ $status === 'ignored' ? 'selected' : '' }}>Ignoradas</option>
                    </select>
                </div>

                <!-- Módulo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Módulo</label>
                    <select name="module" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                        <option value="">Todos</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                    <select name="type" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                        <option value="">Todos</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-2 mt-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                    🔍 Filtrar
                </button>
                <a href="{{ route('core.notifications.index') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-semibold transition">
                    ✕ Limpar
                </a>
            </div>
        </form>

        <!-- LISTA DE NOTIFICAÇÕES -->
        @if ($notifications->count() === 0)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">📭 Nenhuma notificação encontrada.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($notifications as $notif)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-4 
                                {{ $notif->status === 'active' ? 'border-l-4 border-l-blue-500' : '' }}
                                {{ $notif->status === 'checked' ? 'opacity-60' : '' }}
                                hover:shadow-md transition">
                        
                        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                            <!-- Conteúdo -->
                            <div class="flex-1">
                                <div class="flex items-start gap-2 mb-1">
                                    <!-- Badge do status -->
                                    @if($notif->status === 'active')
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold rounded">
                                            Ativa
                                        </span>
                                    @elseif($notif->status === 'checked')
                                        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-semibold rounded">
                                            Lida
                                        </span>
                                    @elseif($notif->status === 'ignored')
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded">
                                            Ignorada
                                        </span>
                                    @endif

                                    <!-- Badge do módulo -->
                                    <span class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-semibold rounded">
                                        {{ ucfirst($notif->module) }}
                                    </span>

                                    <!-- Badge do tipo -->
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded">
                                        {{ $notif->type }}
                                    </span>
                                </div>

                                <h3 class="font-semibold text-base text-gray-800 dark:text-white mb-1">
                                    {{ $notif->title }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                    {{ $notif->message }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    📅 {{ $notif->triggered_at?->format('d/m/Y H:i') }}
                                </p>

                                @if($notif->url)
                                    <a href="{{ $notif->url }}" class="inline-block mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        → Ver detalhes
                                    </a>
                                @endif
                            </div>

                            <!-- Ações -->
                            @if($notif->status === 'active')
                                <div class="flex sm:flex-col gap-2">
                                    <form method="POST" action="{{ route('core.notifications.check', $notif->id) }}">
                                        @csrf
                                        <button class="w-full px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-md transition whitespace-nowrap">
                                            ✓ Lida
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('core.notifications.ignore', $notif->id) }}">
                                        @csrf
                                        <button class="w-full px-3 py-1.5 text-xs bg-red-600 hover:bg-red-700 text-white rounded-md transition whitespace-nowrap">
                                            ✕ Ignorar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- PAGINAÇÃO -->
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
@endsection