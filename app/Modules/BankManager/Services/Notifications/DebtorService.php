<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Debtors\Debtor;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;

class DebtorService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 4️⃣ DEVEDORES
     * Lembrar de cobrar pessoas que devem dinheiro (D-5, D-1, D0)
     */
    public function remindDueDebtors(int $userId): void
    {
        $debtors = Debtor::where('user_id', $userId)
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->get();

        $today = Carbon::today();
        $alertMoments = [
            ['days' => 5, 'label' => 'in_5_days', 'icon' => '⏳', 'message' => 'Em 5 dias'],
            ['days' => 1, 'label' => 'tomorrow', 'icon' => '🔔', 'message' => 'Lembrete: pagamento vence amanhã!'],
            ['days' => 0, 'label' => 'today', 'icon' => '📅', 'message' => 'Pagamento HOJE'],
        ];

        foreach ($debtors as $debtor) {
            $dueDate = Carbon::parse($debtor->due_date);
            $daysUntil = $today->diffInDays($dueDate, false);

            foreach ($alertMoments as $moment) {
                if ($daysUntil == $moment['days']) {
                    $context = "debtor_{$debtor->id}_{$moment['label']}";

                    if (BankManagerNotification::existsActive($context)) {
                        continue;
                    }

                    $title = $moment['icon'] . ' Cobrança: ' . $debtor->debtor_name;
                    $message = $moment['message'];
                    
                    if ($moment['days'] === 5) {
                        $message = "{$moment['icon']} {$moment['message']}: {$debtor->debtor_name}";
                    } elseif ($moment['days'] === 0) {
                        $message = "{$moment['icon']} {$moment['message']}: {$debtor->debtor_name}";
                    }

                    if ($debtor->amount) {
                        $message .= " - €" . number_format($debtor->amount, 2);
                    }

                    $notification = BankManagerNotification::create([
                        'user_id' => $userId,
                        'type' => 'debtor',
                        'title' => $title,
                        'message' => $message,
                        'context' => $context,
                        'data' => [
                            'debtor_id' => $debtor->id,
                            'debtor_name' => $debtor->debtor_name,
                            'amount' => $debtor->amount,
                            'due_date' => $debtor->due_date,
                            'days_until' => $moment['days'],
                            'alert_moment' => $moment['label'],
                        ],
                        'link' => route('bank-manager.debtors.index'),
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
