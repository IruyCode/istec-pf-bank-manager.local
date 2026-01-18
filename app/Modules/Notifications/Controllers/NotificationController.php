<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Modules\Notifications\Models\CoreNotification; 

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = CoreNotification::orderByDesc('created_at');

        // Filtro por status
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filtro por módulo
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate(15);

        // Estatísticas
        $stats = [
            'total' => CoreNotification::count(),
            'active' => CoreNotification::where('status', 'active')->count(),
            'checked' => CoreNotification::where('status', 'checked')->count(),
            'ignored' => CoreNotification::where('status', 'ignored')->count(),
        ];

        // Opções para filtros
        $modules = CoreNotification::distinct()->pluck('module');
        $types = CoreNotification::distinct()->pluck('type');

        return view('notifications::index', compact('notifications', 'stats', 'modules', 'types', 'status'));
    }

    public function markAsChecked(int $id)
    {
        $notif = CoreNotification::findOrFail($id);
        $notif->markChecked();
        return back()->with('success', 'Notificação marcada como lida.');
    }

    public function markAsIgnored(int $id)
    {
        $notif = CoreNotification::findOrFail($id);
        $notif->markIgnored();

        return back()->with('success', 'Notificação ignorada.');
    }

    public function markAllAsChecked()
    {
        CoreNotification::where('status', 'active')->update([
            'status'      => 'checked',
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Todas as notificações foram marcadas como lidas.');
    }
}
