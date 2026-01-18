<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Transaction;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpendingAlertService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 7️⃣ ALERTAS DE GASTOS
     * Alertar quando gastos mensais excedem limites comparados ao mês anterior
     * Faixas: 70%, 90%, 100%
     * Exclui despesas fixas e parcelas
     */
    public function checkSpendingAlerts(int $userId): void
    {
        $today = Carbon::today();
        $currentMonth = $today->copy()->startOfMonth();
        $lastMonth = $today->copy()->subMonth()->startOfMonth();

        // Gastos do mês atual (exclui despesas fixas e parcelas)
        $currentMonthSpending = Transaction::where('user_id', $userId)
            ->where('is_recurring', false) // Exclui despesas fixas
            ->whereNull('debt_installment_id') // Exclui parcelas
            ->whereYear('transaction_date', $currentMonth->year)
            ->whereMonth('transaction_date', $currentMonth->month)
            ->sum('amount');

        // Gastos do mês anterior (exclui despesas fixas e parcelas)
        $lastMonthSpending = Transaction::where('user_id', $userId)
            ->where('is_recurring', false)
            ->whereNull('debt_installment_id')
            ->whereYear('transaction_date', $lastMonth->year)
            ->whereMonth('transaction_date', $lastMonth->month)
            ->sum('amount');

        // Se não houve gastos no mês anterior, não há comparação
        if ($lastMonthSpending <= 0) {
            return;
        }

        $percentage = ($currentMonthSpending / $lastMonthSpending) * 100;

        // Define faixas de alerta
        $alerts = [
            ['threshold' => 100, 'icon' => '❗', 'title' => 'Você atingiu o nível médio!', 'message' => 'Seus gastos este mês igualaram ou ultrapassaram o mês anterior.'],
            ['threshold' => 90, 'icon' => '🚨', 'title' => 'Quase atingindo seu limite!', 'message' => 'Você já gastou 90% do que gastou no mês passado. Planeje-se para não ultrapassar.'],
            ['threshold' => 70, 'icon' => '⚠️', 'title' => 'Atenção aos seus gastos!', 'message' => 'Você já gastou 70% do que gastou no mês passado. Considere reduzir gastos não essenciais.'],
        ];

        $yearMonth = $today->format('Ym');

        foreach ($alerts as $alert) {
            if ($percentage >= $alert['threshold']) {
                $context = "spending_alert_{$yearMonth}_{$alert['threshold']}";

                // Envia apenas 1 notificação por faixa por mês
                if (BankManagerNotification::existsActive($context)) {
                    continue;
                }

                // Se ultrapassar 100%, envia apenas notificação de 100% (não múltiplas)
                if ($percentage >= 100 && $alert['threshold'] < 100) {
                    continue;
                }

                $notification = BankManagerNotification::create([
                    'user_id' => $userId,
                    'type' => 'spending',
                    'title' => $alert['icon'] . ' ' . $alert['title'],
                    'message' => $alert['message'] . " (€" . number_format($currentMonthSpending, 2) . " / €" . number_format($lastMonthSpending, 2) . ")",
                    'context' => $context,
                    'data' => [
                        'current_month_spending' => $currentMonthSpending,
                        'last_month_spending' => $lastMonthSpending,
                        'percentage' => round($percentage, 2),
                        'threshold' => $alert['threshold'],
                        'month' => $currentMonth->format('Y-m'),
                    ],
                    'link' => '/bankmanager/reports',
                ]);

                $this->pushService->sendToUser($userId, [
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'data' => $notification->data,
                ]);

                // Se enviou alerta de 100%, para aqui (não envia 90% e 70%)
                if ($alert['threshold'] == 100) {
                    break;
                }
            }
        }
    }
}
