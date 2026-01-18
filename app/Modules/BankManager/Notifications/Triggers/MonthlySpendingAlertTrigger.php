<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Transaction;
use App\Modules\BankManager\Models\OperationCategory;
use App\Modules\Notifications\Models\CoreNotification;

use Carbon\Carbon;

class MonthlySpendingAlertTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'monthly_spending_alert';
    }

    public function shouldTrigger(): bool
    {
        return true; // Dispara diariamente, mas notifica somente quando necessário
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $now = Carbon::now($tz);

        // Períodos
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $lastMonthStart = $monthStart->copy()->subMonth();
        $lastMonthEnd   = $monthEnd->copy()->subMonth();

        // Categorias de despesas (operation_type_id = 2)
        $validCategoryIds = OperationCategory::query()
            ->where('operation_type_id', 2)
            ->whereNotIn('name', [
                'Despesas Fixas',
                'Parcelas',
                'Metas_Expenses',
                'Metas',
            ])
            ->pluck('id')
            ->toArray();

        if (empty($validCategoryIds)) {
            return;
        }

        // Totais
        $lastMonthTotal = Transaction::whereIn('operation_category_id', $validCategoryIds)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        if ($lastMonthTotal <= 0) {
            return; // Não há histórico
        }

        $currentTotal = Transaction::whereIn('operation_category_id', $validCategoryIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $percent = ($currentTotal / $lastMonthTotal) * 100;

        // Limites
        $thresholds = [
            70 => [
                'title' => '⚠️ Atenção aos seus gastos!',
                'message' => 'Você já atingiu 70% dos gastos do mês anterior. Reduza gastos se possível.',
            ],
            90 => [
                'title' => '🚨 Gastos elevados!',
                'message' => 'Você já atingiu 90% da média anterior. Monitore suas despesas com atenção.',
            ],
            100 => [
                'title' => '❗ Você atingiu o mesmo nível de gastos do mês passado!',
                'message' => 'Você igualou ou ultrapassou o nível de gastos anterior. Reduza imediatamente.',
            ],
        ];

        // Descobrir maior limite atingido
        $triggerLevel = null;
        foreach ([70, 90, 100] as $limit) {
            if ($percent >= $limit) {
                $triggerLevel = $limit;
            }
        }

        if (!$triggerLevel) {
            return; // Nenhum nível atingido
        }

        // Contexto único
        $context = "spending_alert_{$now->format('Ym')}_{$triggerLevel}";

        // Evitar duplicados
        if (CoreNotification::where('context', $context)->exists()) {
            return;
        }

        // Criar notificação
        $service->notify([
            'module'  => 'bank-manager',
            'type'    => 'spending',
            'context' => $context,
            'title'   => $thresholds[$triggerLevel]['title'],
            'message' => $thresholds[$triggerLevel]['message'],
            'url'     => '/admin/bank-manager/transactions',
        ]);
    }
}
