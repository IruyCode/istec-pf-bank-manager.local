<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;

use App\Http\Middleware\CheckUserType;
use App\Http\Controllers\UserController;

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('password.email');

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    // Rotas de admin removidas. Todas as rotas principais agora são pelo Bank Manager.

    Route::prefix('client')
        ->name('client.')
        ->middleware([CheckUserType::class])
        ->group(function () {
            Route::get('/', [UserController::class, 'dashboardClient'])->name('dashboardClient');
        });

    require app_path('Modules/BankManager/routes.php');
});


