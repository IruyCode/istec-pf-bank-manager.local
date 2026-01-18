<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\BankManager\Models\BankManagerNotification;

class BankManagerNotificationPolicy
{
    /**
     * Determina se o usuário pode visualizar a notificação
     */
    public function view(User $user, BankManagerNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determina se o usuário pode atualizar a notificação
     */
    public function update(User $user, BankManagerNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    /**
     * Determina se o usuário pode deletar a notificação
     */
    public function delete(User $user, BankManagerNotification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}
