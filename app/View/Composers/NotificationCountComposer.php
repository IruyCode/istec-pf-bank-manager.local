<?php

namespace App\View\Composers;

use Illuminate\View\View;
use App\Modules\Notifications\Models\CoreNotification;
use Illuminate\Support\Facades\Auth;

class NotificationCountComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $unreadCount = CoreNotification::where('status', 'active')->count();
            $view->with('unreadNotificationsCount', $unreadCount);
        } else {
            $view->with('unreadNotificationsCount', 0);
        }
    }
}
