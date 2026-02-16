<?php

namespace App\Modules\BankManager\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\BankManager\Models\AccountBalance;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function users()
    {
        // Verifica se o usuário autenticado é admin
        if (!Gate::allows('viewAdmin', Auth::user())) {
            abort(403, 'Não tem permissão para acessar esta página.');
        }

        // Obtém todos os usuários (exceto o admin atual se desejar)
        $users = User::all();

        // Mapeia os dados dos usuários com informações de contas e último acesso
        $usersData = $users->map(function ($user) {
            // Contar quantas contas cada usuário tem
            $accountCount = AccountBalance::where('user_id', $user->id)->count();

            // Obter a última data de login (usando updated_at como proxy)
            $lastLogin = $user->updated_at;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type_user_id' => $user->type_user_id,
                'account_count' => $accountCount,
                'last_login' => $lastLogin,
                'created_at' => $user->created_at,
            ];
        });

        return view('bankmanager::admin.users', [
            'users' => $usersData,
        ]);
    }
}
