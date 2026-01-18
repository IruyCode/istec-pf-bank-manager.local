<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Goals\FinancialGoals;
use App\Modules\BankManager\Models\Goals\GoalTransaction;
use App\Modules\Notifications\Models\CoreNotification;

use Carbon\Carbon;

class ReminderGoalsMonthlyTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'goals_monthly_reminder';
    }

    public function shouldTrigger(): bool
    {
        // Apenas dispara nos dias 5, 10 e 20
        return in_array((int)now()->day, [5, 10, 20], true);
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $now = Carbon::now($tz);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $goals = FinancialGoals::where('is_completed', false)->get();

        if ($goals->isEmpty()) {
            return;
        }

        $positiveTypes = ['add', 'deposit', 'increase']; // ajustes permitidos

        foreach ($goals as $goal) {

            // Já teve contribuição POSITIVA no mês?
            $hasPositive = GoalTransaction::where('goal_id', $goal->id)
                ->whereIn('type', $positiveTypes)
                ->whereBetween('performed_at', [$monthStart, $monthEnd])
                ->exists();

            if ($hasPositive) {
                continue;
            }

            // Criar contexto único por meta + dia
            $context = "goal_{$goal->id}_no_contrib_{$now->format('Ymd')}";

            $exists = CoreNotification::where('context', $context)
                ->exists();

            if ($exists) {
                continue;
            }

            // Criar notificação
            $service->notify([
                'module'  => 'bank-manager',
                'type'    => 'goal',
                'context' => $context,
                'title'   => "🎯 Contribua para a meta: {$goal->name}",
                'message' => "Ainda não houve contribuição para a meta **{$goal->name}** neste mês.",
                'url'     => '/admin/bank-manager/goals',
            ]);
        }
    }
}
// 