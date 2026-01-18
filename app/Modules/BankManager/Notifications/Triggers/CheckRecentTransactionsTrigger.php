<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Transaction;

class CheckRecentTransactionsTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'missing_transactions';
    }

    public function shouldTrigger(): bool
    {
        // Última transação registrada (income ou expense)
        $last = Transaction::latest('created_at')->first();

        // Se nunca houve transações → considera como "precisa lembrar"
        if (!$last) {
            return true;
        }

        // Quantos dias desde a última transação?
        return $last->created_at->diffInDays(now()) >= 3;
    }

    public function run(NotificationService $service): void
    {
        // Será criada apenas 1 vez por dia, mesmo se usuário marcar como lida
        $service->notifyOnce([
            'module'  => 'bank-manager',
            'context' => 'missing_transactions',
            'type'    => 'reminder',
            'title'   => 'Registre suas transações recentes',
            'message' => 'Você não registra despesas ou receitas há 3 dias. Adicione uma nova transação para manter seu controle financeiro atualizado.',
        ]);
    }
}
