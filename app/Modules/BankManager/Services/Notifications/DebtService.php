<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Debts\Debt;
use App\Modules\BankManager\Models\Debts\DebtInstallment;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;

class DebtService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 5️⃣ DÍVIDAS (PARCELAS)
     * Lembrar de pagar parcelas de dívidas
     * A) LEMBRETES FUTUROS (D-7, D-2, D-1, D0)
     * B) PARCELAS ATRASADAS (D+5, D+10, D+11+)
     */
    public function remindDueInstallments(int $userId): void
    {
        $installments = DebtInstallment::whereHas('debt', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereNull('paid_at')
        ->whereNotNull('due_date')
        ->with('debt')
        ->get();

        $today = Carbon::today();

        foreach ($installments as $installment) {
            $dueDate = Carbon::parse($installment->due_date);
            $daysUntil = $today->diffInDays($dueDate, false);

            // A) LEMBRETES FUTUROS
            if ($daysUntil >= 0) {
                $this->sendFutureReminder($userId, $installment, $daysUntil);
            }
            // B) PARCELAS ATRASADAS
            else {
                $this->sendLateReminder($userId, $installment, abs($daysUntil));
            }
        }
    }

    /**
     * Lembretes futuros (D-7, D-2, D-1, D0)
     */
    private function sendFutureReminder(int $userId, DebtInstallment $installment, int $daysUntil): void
    {
        $alertMoments = [
            ['days' => 7, 'icon' => '⏳', 'message' => 'Parcela em 7 dias'],
            ['days' => 2, 'icon' => '⏰', 'message' => 'Parcela em 2 dias'],
            ['days' => 1, 'icon' => '🔔', 'message' => 'Amanhã vence'],
            ['days' => 0, 'icon' => '📅', 'message' => 'Pagamento HOJE'],
        ];

        foreach ($alertMoments as $moment) {
            if ($daysUntil == $moment['days']) {
                $context = "debt_{$installment->debt_id}_inst{$installment->installment_number}_day_{$moment['days']}";

                // Evita duplicação no mesmo dia
                $existing = BankManagerNotification::where('context', $context)
                    ->where('triggered_at', '>=', today())
                    ->first();

                if ($existing) {
                    continue;
                }

                $title = $moment['icon'] . ' Parcela ' . $installment->installment_number;
                $message = "{$moment['message']}: {$installment->debt->name}";
                
                if ($installment->amount) {
                    $message .= " - €" . number_format($installment->amount, 2);
                }

                $notification = BankManagerNotification::create([
                    'user_id' => $userId,
                    'type' => 'debt',
                    'title' => $title,
                    'message' => $message,
                    'context' => $context,
                    'data' => [
                        'debt_id' => $installment->debt_id,
                        'debt_name' => $installment->debt->name,
                        'installment_id' => $installment->id,
                        'installment_number' => $installment->installment_number,
                        'amount' => $installment->amount,
                        'due_date' => $installment->due_date,
                        'days_until' => $moment['days'],
                        'status' => 'upcoming',
                    ],
                    'link' => '/bankmanager/debts/' . $installment->debt_id,
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

    /**
     * Parcelas atrasadas (D+5, D+10, diariamente após D+11)
     */
    private function sendLateReminder(int $userId, DebtInstallment $installment, int $daysLate): void
    {
        $shouldAlert = false;
        $alertMoment = null;

        if ($daysLate == 5) {
            $shouldAlert = true;
            $alertMoment = 5;
        } elseif ($daysLate == 10) {
            $shouldAlert = true;
            $alertMoment = 10;
        } elseif ($daysLate >= 11) {
            $shouldAlert = true;
            $alertMoment = $daysLate;
        }

        if (!$shouldAlert) {
            return;
        }

        $context = "debt_{$installment->debt_id}_inst{$installment->installment_number}_late_{$alertMoment}";

        // Para D+11+, verifica se já notificou hoje
        if ($daysLate >= 11) {
            $existing = BankManagerNotification::where('context', 'LIKE', "debt_{$installment->debt_id}_inst{$installment->installment_number}_late_%")
                ->where('triggered_at', '>=', today())
                ->first();

            if ($existing) {
                return;
            }
        } else {
            if (BankManagerNotification::existsActive($context)) {
                return;
            }
        }

        $title = '⚠️ Parcela Atrasada';
        $message = "Parcela {$installment->installment_number} atrasada há {$daysLate} dia(s): {$installment->debt->name}";
        
        if ($installment->amount) {
            $message .= " - €" . number_format($installment->amount, 2);
        }

        $notification = BankManagerNotification::create([
            'user_id' => $userId,
            'type' => 'debt',
            'title' => $title,
            'message' => $message,
            'context' => $context,
            'data' => [
                'debt_id' => $installment->debt_id,
                'debt_name' => $installment->debt->name,
                'installment_id' => $installment->id,
                'installment_number' => $installment->installment_number,
                'amount' => $installment->amount,
                'due_date' => $installment->due_date,
                'days_late' => $daysLate,
                'status' => 'overdue',
            ],
            'link' => '/bankmanager/debts/' . $installment->debt_id,
        ]);

        $this->pushService->sendToUser($userId, [
            'title' => $notification->title,
            'message' => $notification->message,
            'link' => $notification->link,
            'data' => $notification->data,
        ]);
    }
}
