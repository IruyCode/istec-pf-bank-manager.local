<?php

namespace App\Policies;

use App\Models\User;

class UserAdminPolicy
{
    /**
     * Determina se o usuário é administrador
     */
    public function viewAdmin(User $user): bool
    {
        return $user->type_user_id === 1;
    }
}
