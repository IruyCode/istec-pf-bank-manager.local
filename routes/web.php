<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\CheckUserType;
use App\Http\Controllers\UserController;

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


