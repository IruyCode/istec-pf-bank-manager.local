<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Transaction;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;

class ExpenseService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 1️⃣ DESPESAS RECENTES
     * Alertar quando o usuário não registra despesas há 2+ dias
     */
    public function checkRecentExpenses(int $userId): void
    {
        // Busca a última transação do tipo "expense" (operation_type_id = 2)
        $lastExpense = Transaction::where('user_id', $userId)
            ->where('operation_type_id', 2)
            ->orderBy('transaction_date', 'desc')
            ->first();

        // Se não encontrou despesa ou passou 2+ dias
        if (!$lastExpense || $lastExpense->transaction_date->diffInDays(now()) >= 2) {
            $context = 'missing_expenses';
            
            // Verifica se já existe notificação ativa
            if (BankManagerNotification::existsActive($context)) {
                return;
            }

            $daysSince = $lastExpense ? $lastExpense->transaction_date->diffInDays(now()) : null;

            $notification = BankManagerNotification::create([
                'user_id' => $userId,
                'type' => 'expense_recent',
                'title' => '💰 Verifique suas despesas!',
                'message' => $daysSince 
                    ? "Já passaram {$daysSince} dias desde sua última despesa registrada. Não se esqueça de manter suas finanças em dia!"
                    : "Você ainda não registrou nenhuma despesa. Mantenha suas finanças em dia!",
                'context' => $context,
                'data' => [
                    'last_expense_date' => $lastExpense?->transaction_date?->format('Y-m-d'),
                    'days_since' => $daysSince,
                ],
                'link' => route('bank-manager.index'),
            ]);

            // Enviar push notification
            $this->pushService->sendToUser($userId, [
                'title' => $notification->title,
                'message' => $notification->message,
                'link' => $notification->link,
                'data' => $notification->data,
            ]);
        }
    }

    /**
     * 2️⃣ DESPESAS FIXAS PRÓXIMAS
     * Lembrar de despesas fixas próximas ao vencimento (D-10, D-5, D-1)
     */
    public function checkUpcomingFixedExpenses(int $userId): void
    {
        $fixedExpenses = Transaction::where('user_id', $userId)
            ->where('is_recurring', true)
            ->where('operation_type_id', 2) // Apenas despesas
            ->get();

        $today = Carbon::today();
        $alertDays = [10, 5, 1]; // D-10, D-5, D-1

        foreach ($fixedExpenses as $expense) {
            if (!$expense->due_day) {
                continue;
            }

            // Próxima data de vencimento
            $dueDate = Carbon::create($today->year, $today->month, $expense->due_day);
            
            // Se o dia já passou neste mês, considera o próximo mês
            if ($dueDate->isPast()) {
                $dueDate->addMonth();
            }

            // Verifica se o dia existe no mês (evita 31 de fevereiro)
            if ($dueDate->day != $expense->due_day) {
                continue;
            }

            $daysUntil = $today->diffInDays($dueDate, false);

            // Verifica se está em um dos dias de alerta
            foreach ($alertDays as $alertDay) {
                if ($daysUntil == $alertDay) {
                    $context = "fixed_expense_{$expense->id}_day_{$alertDay}";
                    
                    if (BankManagerNotification::existsActive($context)) {
                        continue;
                    }

                    $messages = [
                        10 => "⏳ Em 10 dias: {$expense->description}",
                        5 => "⏰ Em 5 dias: {$expense->description}",
                        1 => "🔔 Amanhã vence: {$expense->description}",
                    ];

                    $notification = BankManagerNotification::create([
                        'user_id' => $userId,
                        'type' => 'expense_fixed',
                        'title' => 'Despesa Fixa Próxima',
                        'message' => $messages[$alertDay] . ($expense->amount ? " - €" . number_format($expense->amount, 2) : ''),
                        'context' => $context,
                        'data' => [
                            'expense_id' => $expense->id,
                            'description' => $expense->description,
                            'amount' => $expense->amount,
                            'due_day' => $expense->due_day,
                            'due_date' => $dueDate->format('Y-m-d'),
                            'days_until' => $alertDay,
                        ],
                        'link' => route('bank-manager.index'),
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
    }
}
