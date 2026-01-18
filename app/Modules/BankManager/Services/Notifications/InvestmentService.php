<?php

namespace App\Modules\BankManager\Services\Notifications;

use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\Investments\Investment;
use App\Modules\BankManager\Services\PushNotificationService;
use Carbon\Carbon;

class InvestmentService
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

    /**
     * 3️⃣ INVESTIMENTOS
     * Lembrar diariamente de atualizar saldos de investimentos
     */
    public function remindDailyUpdate(int $userId): void
    {
        // Verifica se existem investimentos ativos
        $activeInvestments = Investment::where('user_id', $userId)
            ->where('is_active', true)
            ->count();

        if ($activeInvestments === 0) {
            return;
        }

        // Context único por data (evita múltiplas notificações no mesmo dia)
        $context = 'investments_update_reminder_' . now()->format('Ymd');

        if (BankManagerNotification::existsActive($context)) {
            return;
        }

        $notification = BankManagerNotification::create([
            'user_id' => $userId,
            'type' => 'investment',
            'title' => '💼 Atualize seus investimentos',
            'message' => "Você tem {$activeInvestments} investimento(s) ativo(s). Não se esqueça de atualizar os saldos hoje!",
            'context' => $context,
            'data' => [
                'active_count' => $activeInvestments,
                'date' => now()->format('Y-m-d'),
            ],
            'link' => '/bankmanager/investments',
        ]);

        $this->pushService->sendToUser($userId, [
            'title' => $notification->title,
            'message' => $notification->message,
            'link' => $notification->link,
            'data' => $notification->data,
        ]);
    }
}
