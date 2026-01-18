<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\Debtors\Debtor;
use App\Modules\Notifications\Models\CoreNotification;

use Carbon\Carbon;

class ReminderDebtorsTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'due_debtors';
    }

    public function shouldTrigger(): bool
    {
        // Este trigger SEMPRE roda, porque ele avalia vários casos.
        return true;
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $today = Carbon::now($tz)->startOfDay();

        $targets = [
            ['label' => 'today',    'date' => $today],
            ['label' => 'tomorrow', 'date' => $today->copy()->addDay()],
            ['label' => 'D5',       'date' => $today->copy()->addDays(5)],
        ];

        foreach ($targets as $item) {

            $label = $item['label'];
            $targetDate = $item['date']->toDateString();

            $debtors = Debtor::where('is_paid', false)
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($debtors as $debtor) {

                // ---- Evitar duplicação ----
                $context = "debtor_{$debtor->id}_{$label}";
                $exists = CoreNotification::where('context', $context)
                    ->whereDate('triggered_at', now()->toDateString())
                    ->exists();

                if ($exists) continue;

                // ---- Criar título e mensagem ----
                switch ($label) {
                    case 'today':
                        $title = "📅 Pagamento HOJE: {$debtor->name}";
                        $message = "Hoje vence o pagamento de {$debtor->name}.";
                        break;

                    case 'tomorrow':
                        $title = "🔔 Amanhã vence: {$debtor->name}";
                        $message = "O pagamento de {$debtor->name} vence amanhã ({$item['date']->format('d/m')}).";
                        break;

                    default: // D5
                        $title = "⏳ Em 5 dias: {$debtor->name}";
                        $message = "O pagamento de {$debtor->name} vence em 5 dias ({$item['date']->format('d/m')}).";
                }

                if ($debtor->amount) {
                    $message .= " Valor: €" . number_format($debtor->amount, 2, ',', '.');
                }

                // ---- Cria notificação ----
                $service->notify([
                    'module'  => 'bank-manager',
                    'type'    => 'debtor',
                    'context' => $context,
                    'title'   => $title,
                    'message' => $message,
                    'url'     => '/admin/bank-manager/debtors',
                ]);
            }
        }
    }
}
