@extends('layout.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🔔 Notificações</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Acompanhe todas as suas notificações financeiras</p>
        </div>
        
        <div class="flex gap-3">
            @if($notifications->where('is_read', false)->count() > 0)
                <button 
                    onclick="markAllAsRead()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    ✓ Marcar todas como lidas
                </button>
            @endif
            <a href="{{ route('bank-manager.index') }}" 
               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                ← Voltar
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <select name="type" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todos os tipos</option>
                <option value="expense_recent" {{ request('type') == 'expense_recent' ? 'selected' : '' }}>💰 Despesas Recentes</option>
                <option value="expense_fixed" {{ request('type') == 'expense_fixed' ? 'selected' : '' }}>📅 Despesas Fixas</option>
                <option value="investment" {{ request('type') == 'investment' ? 'selected' : '' }}>💼 Investimentos</option>
                <option value="debtor" {{ request('type') == 'debtor' ? 'selected' : '' }}>👤 Devedores</option>
                <option value="debt" {{ request('type') == 'debt' ? 'selected' : '' }}>💳 Dívidas</option>
                <option value="goal" {{ request('type') == 'goal' ? 'selected' : '' }}>🎯 Metas</option>
                <option value="spending" {{ request('type') == 'spending' ? 'selected' : '' }}>📊 Alertas de Gastos</option>
            </select>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="unread" value="1" {{ request('unread') ? 'checked' : '' }} class="rounded">
                <span class="text-gray-700 dark:text-gray-300">Apenas não lidas</span>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="active" value="1" {{ request('active') ? 'checked' : '' }} class="rounded">
                <span class="text-gray-700 dark:text-gray-300">Apenas ativas</span>
            </label>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                🔍 Filtrar
            </button>

            @if(request()->hasAny(['type', 'unread', 'active']))
                <a href="{{ route('bank-manager.notifications.index') }}" 
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    ✕ Limpar filtros
                </a>
            @endif
        </form>
    </div>

    <!-- Lista de Notificações -->
    @if($notifications->count() > 0)
        <div class="space-y-4">
            @foreach($notifications as $notification)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-5 border-l-4 
                    {{ $notification->is_read ? 'border-gray-300 dark:border-gray-600 opacity-75' : 'border-blue-500' }}
                    {{ $notification->is_dismissed ? 'opacity-50' : '' }}">
                    
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <!-- Título -->
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $notification->title }}
                                </h3>
                                @if(!$notification->is_read)
                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full">
                                        Nova
                                    </span>
                                @endif
                            </div>

                            <!-- Mensagem -->
                            <p class="text-gray-700 dark:text-gray-300 mb-3">
                                {{ $notification->message }}
                            </p>

                            <!-- Metadados -->
                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>📅 {{ $notification->triggered_at->format('d/m/Y H:i') }}</span>
                                <span>🏷️ {{ ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="flex flex-col gap-2 ml-4">
                            @if($notification->link)
                                <a href="{{ $notification->link }}" 
                                   class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition text-center">
                                    Ver detalhes
                                </a>
                            @endif

                            @if(!$notification->is_read)
                                <button 
                                    onclick="markAsRead({{ $notification->id }})"
                                    class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                    ✓ Marcar lida
                                </button>
                            @endif

                            @if(!$notification->is_dismissed)
                                <button 
                                    onclick="dismiss({{ $notification->id }})"
                                    class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition">
                                    ✕ Dispensar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
            <div class="text-6xl mb-4">🔔</div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                Nenhuma notificação encontrada
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                {{ request()->hasAny(['type', 'unread', 'active']) 
                    ? 'Tente ajustar os filtros para ver mais notificações.' 
                    : 'Você está em dia com suas finanças!' }}
            </p>
        </div>
    @endif
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/bank-manager/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('/bank-manager/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function dismiss(notificationId) {
    fetch(`/bank-manager/notifications/${notificationId}/dismiss`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endsection
