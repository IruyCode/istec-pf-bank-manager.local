<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Goals\FinancialGoals;
use App\Modules\BankManager\Models\Goals\GoalTransaction;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;

class GoalService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 6️⃣ METAS FINANCEIRAS
     * Lembrar de contribuir para metas mensalmente (dias 5, 10 e 20)
     */
    public function remindMonthlyContributions(int $userId): void
    {
        $today = Carbon::today();
        $allowedDays = [5, 10, 20];

        // Executa apenas nos dias permitidos
        if (!in_array($today->day, $allowedDays)) {
            return;
        }

        $goals = FinancialGoals::where('user_id', $userId)
            ->where('is_completed', false)
            ->get();

        foreach ($goals as $goal) {
            // Ignora metas que já atingiram o objetivo
            if ($goal->current_amount >= $goal->target_amount) {
                continue;
            }

            // Verifica se já houve contribuição neste mês
            $hasContributionThisMonth = GoalTransaction::where('goal_id', $goal->id)
                ->whereYear('performed_at', $today->year)
                ->whereMonth('performed_at', $today->month)
                ->where('type', 'aporte') // Tipo 'aporte' ao invés de 'add/deposit/increase'
                ->exists();

            if ($hasContributionThisMonth) {
                continue;
            }

            $context = "goal_{$goal->id}_no_contrib_" . $today->format('Ymd');

            if (BankManagerNotification::existsActive($context)) {
                continue;
            }

            $remaining = $goal->target_amount - $goal->current_amount;
            $percentComplete = $goal->target_amount > 0 
                ? round(($goal->current_amount / $goal->target_amount) * 100, 1)
                : 0;

            $notification = BankManagerNotification::create([
                'user_id' => $userId,
                'type' => 'goal',
                'title' => '🎯 Lembrete: Meta Financeira',
                'message' => "Não se esqueça de contribuir para '{$goal->name}'. Você está a {$percentComplete}% da meta!",
                'context' => $context,
                'data' => [
                    'goal_id' => $goal->id,
                    'goal_name' => $goal->name,
                    'target_amount' => $goal->target_amount,
                    'current_amount' => $goal->current_amount,
                    'remaining' => $remaining,
                    'percent_complete' => $percentComplete,
                    'reminder_day' => $today->day,
                ],
                'link' => route('bank-manager.goals.index'),
            ]);

            $this->pushService->sendToUser($userId, [
                'title' => $notification->title,
                'message' => $notification->message,
                'link' => $notification->link,
                'data' => $notification->data,
            ]);
        }
    }
}
