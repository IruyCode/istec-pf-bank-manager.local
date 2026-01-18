<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Notifications\Models\CoreNotification;

class UserController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function dashboardClient()
    {
        return view('dashboard-client');
    }

    public function dashboardAdmin()
    {
        // Buscar notificações ativas agrupadas por módulo
        $notifications = CoreNotification::where('status', 'active')
            ->orderByDesc('triggered_at')
            ->take(20)
            ->get()
            ->groupBy('module');

        // Contar notificações por módulo
        $notificationCounts = [
            'bank-manager' => CoreNotification::where('status', 'active')->where('module', 'bank-manager')->count(),
            'task-manager' => CoreNotification::where('status', 'active')->where('module', 'task-manager')->count(),
            'total' => CoreNotification::where('status', 'active')->count(),
        ];

        return view('dashboard-admin', compact('notifications', 'notificationCounts'));
    }
}
