<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Transaction;
use Carbon\Carbon;

class AbnormalExpenseTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'abnormal_expense';
    }

    /**
     * Sempre rodamos - a filtragem interna decide se deve disparar ou não.
     */
    public function shouldTrigger(): bool
    {
        return true;
    }

    public function run(NotificationService $service): void
    {
        $today = Carbon::today();

        //Buscar todas despesas feitas HOJE
        $todayExpenses = Transaction::query()
            ->whereDate('created_at', $today)
            ->whereHas('operationCategory.operationType', fn($q) =>
                $q->where('operation_type', 'expense')
            )
            ->get();

        if ($todayExpenses->isEmpty()) {
            return;
        }

        foreach ($todayExpenses as $expense) {

            $category = $expense->operationSubCategory?->operationCategory;
            if (!$category) {
                continue;
            }

            $categoryId = $category->id;

            // Média dos últimos 90 dias nesta categoria
            $avg = Transaction::query()
                ->where('operation_category_id', $categoryId)
                ->whereBetween('created_at', [now()->subDays(90), now()])
                ->avg('amount');

            if (!$avg || $avg <= 0) {
                continue;
            }

            // Regra de detecção
            $limit = $avg * 2;

            if ($expense->amount <= $limit) {
                continue;
            }

            // Evitar notificações repetidas no mesmo dia
            $context = "abnormal_expense_{$categoryId}_" . $today->format('Ymd');

            $service->notifyOnce([
                'module'  => 'bank-manager',
                'type'    => 'warning',
                'context' => $context,
                'title'   => "Gasto acima da média em {$category->name}",
                'message' => "Você gastou €" . number_format($expense->amount, 2, ',', '.') .
                             " hoje em {$category->name}, mais que o dobro da média dos últimos meses. Está tudo certo?",
            ]);
        }
    }
}