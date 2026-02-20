<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('password.email');

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->route('bank-manager.index');
    });

    require app_path('Modules/BankManager/routes.php');
});


