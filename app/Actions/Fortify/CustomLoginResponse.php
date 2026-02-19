<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Http\Request;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ((int) $user->type_user_id === 1) {
            return redirect()->route('bank-manager.index');
        }

        return redirect()->route('client.dashboardClient');
    }
}
