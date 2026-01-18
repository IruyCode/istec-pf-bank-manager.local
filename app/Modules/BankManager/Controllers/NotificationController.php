<?php

namespace App\Modules\BankManager\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BankManager\Models\BankManagerNotification;
use App\Modules\BankManager\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Lista todas as notificações do usuário
     */
    public function index(Request $request)
    {
        $query = BankManagerNotification::where('user_id', Auth::id())
            ->orderBy('triggered_at', 'desc');

        // Filtros
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('unread')) {
            $query->unread();
        }

        if ($request->has('active')) {
            $query->active();
        }

        $notifications = $query->paginate(20);

        return view('bankmanager::notifications.index', compact('notifications'));
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(BankManagerNotification $notification)
    {
        // Verificar se a notificação pertence ao usuário autenticado
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notificação marcada como lida',
        ]);
    }

    /**
     * Marcar todas como lidas
     */
    public function markAllAsRead()
    {
        BankManagerNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Todas as notificações foram marcadas como lidas',
        ]);
    }

    /**
     * Dispensar notificação
     */
    public function dismiss(BankManagerNotification $notification)
    {
        // Verificar se a notificação pertence ao usuário autenticado
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado',
            ], 403);
        }

        $notification->dismiss();

        return response()->json([
            'success' => true,
            'message' => 'Notificação dispensada',
        ]);
    }

    /**
     * Obter contagem de não lidas
     */
    public function unreadCount()
    {
        $count = BankManagerNotification::where('user_id', Auth::id())
            ->unread()
            ->active()
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Registrar token FCM
     */
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $token = FcmToken::registerToken(
            Auth::id(),
            $request->token,
            $request->device_name
        );

        return response()->json([
            'success' => true,
            'message' => 'Token registrado com sucesso',
            'token_id' => $token->id,
        ]);
    }

    /**
     * Remover token FCM
     */
    public function removeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $removed = FcmToken::removeToken(Auth::id(), $request->token);

        return response()->json([
            'success' => $removed,
            'message' => $removed ? 'Token removido com sucesso' : 'Token não encontrado',
        ]);
    }

    /**
     * Listar tokens do usuário
     */
    public function tokens()
    {
        $tokens = FcmToken::where('user_id', Auth::id())
            ->orderBy('last_used_at', 'desc')
            ->get();

        return response()->json([
            'tokens' => $tokens,
        ]);
    }
}
