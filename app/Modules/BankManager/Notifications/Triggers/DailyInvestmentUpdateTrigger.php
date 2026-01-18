<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Investments\Investment;
use App\Modules\Notifications\Models\CoreNotification;

use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DailyInvestmentUpdateTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'daily_investments_update';
    }

    public function shouldTrigger(): bool
    {
        // Sempre dispara 1x por dia
        return true;
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $now = Carbon::now($tz);

        $activeCount = Investment::query()
            ->when(
                Schema::hasColumn('app_bank_manager_investments', 'status'),
                fn($q) => $q->where('status', 'active')
            )
            ->when(
                Schema::hasColumn('app_bank_manager_investments', 'closed_at'),
                fn($q) => $q->orWhereNull('closed_at')
            )
            ->count();

        if ($activeCount === 0) {
            return; // não há investimentos ativos
        }

        // Contexto único por dia
        $context = 'investments_update_' . $now->format('Ymd');

        $exists = CoreNotification::where('context', $context)
            ->exists();

        if ($exists) {
            return; // já criado hoje
        }

        // Criar notificação
        $service->notify([
            'module'  => 'bank-manager',
            'type'    => 'investment',
            'context' => $context,
            'title'   => '📈 Atualize seus investimentos',
            'message' => "Você tem {$activeCount} investimento(s) ativo(s). Atualize o saldo hoje.",
            'url'     => '/admin/bank-manager/investments',
        ]);
    }
}
