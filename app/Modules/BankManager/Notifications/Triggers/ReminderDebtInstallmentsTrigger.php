<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Notifications\Models\CoreNotification;
use App\Modules\BankManager\Models\Debts\DebtInstallment;

use Carbon\Carbon;

class ReminderDebtInstallmentsTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'debt_installments';
    }

    public function shouldTrigger(): bool
    {
        return true; // Sempre roda, a lógica interna decide se notifica.
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $today = Carbon::now($tz)->startOfDay();

        // ---------- 1️⃣ FUTUROS (D7, D2, D1, TODAY) ----------
        $future = [
            ['label' => 'D7', 'days' => 7],
            ['label' => 'D2', 'days' => 2],
            ['label' => 'D1', 'days' => 1],
            ['label' => 'today', 'days' => 0],
        ];

        foreach ($future as $item) {

            $label = $item['label'];
            $targetDate = $today->copy()->addDays($item['days'])->toDateString();

            $installments = DebtInstallment::whereNull('paid_at')
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($installments as $inst) {

                $debt = $inst->debt;
                $dueFormatted = Carbon::parse($inst->due_date)->format('d/m');
                $amount = '€' . number_format($inst->amount, 2, ',', '.');

                // Evitar duplicados
                $context = "debt_{$debt->id}_inst{$inst->installment_number}_{$label}";
                $exists = CoreNotification::where('context', $context)
                    ->whereDate('triggered_at', $today->toDateString())
                    ->exists();

                if ($exists) continue;

                // Mensagens
                switch ($label) {
                    case 'D7':
                        $title   = "⏳ Parcela em 7 dias: {$debt->name}";
                        $message = "A parcela nº {$inst->installment_number} vence em 7 dias ({$dueFormatted}). Valor: {$amount}";
                        break;

                    case 'D2':
                        $title   = "⏰ Parcela em 2 dias: {$debt->name}";
                        $message = "A parcela nº {$inst->installment_number} vence em 2 dias ({$dueFormatted}). Valor: {$amount}";
                        break;

                    case 'D1':
                        $title   = "🔔 Amanhã vence: {$debt->name}";
                        $message = "A parcela nº {$inst->installment_number} vence amanhã ({$dueFormatted}). Valor: {$amount}";
                        break;

                    default: // today
                        $title   = "📅 Pagamento HOJE: {$debt->name}";
                        $message = "A parcela nº {$inst->installment_number} vence hoje ({$dueFormatted}). Valor: {$amount}";
                }

                // Criar a notificação
                $service->notify([
                    'module'  => 'bank-manager',
                    'type'    => 'debt',
                    'context' => $context,
                    'title'   => $title,
                    'message' => $message,
                    'url'     => '/admin/bank-manager/debts',
                ]);
            }
        }

        // ---------- 2️⃣ ATRASADAS (D5, D10, >=11) ----------
        $overdue = DebtInstallment::whereNull('paid_at')
            ->whereDate('due_date', '<', $today->toDateString())
            ->get();

        foreach ($overdue as $inst) {

            $debt   = $inst->debt;
            $amount = '€' . number_format($inst->amount, 2, ',', '.');
            $due    = Carbon::parse($inst->due_date, $tz)->startOfDay();

            $daysLate = $due->diffInDays($today, false);

            // Notificar em: 5 dias, 10 dias e >=11 diariamente
            if (!in_array($daysLate, [5, 10]) && $daysLate < 11) {
                continue;
            }

            $label = "late_{$daysLate}";
            $context = "debt_{$debt->id}_inst{$inst->installment_number}_late_{$daysLate}";

            // Evitar duplicados próximos
            $exists = CoreNotification::where('context', $context)
                ->whereDate('triggered_at', $today->toDateString())
                ->exists();

            if ($exists) continue;

            // Mensagem
            $title = "⚠️ Parcela atrasada: {$debt->name}";
            $message = "A parcela nº {$inst->installment_number} está atrasada há {$daysLate} dia(s). Valor pendente: {$amount}.";

            // Criar notificação
            $service->notify([
                'module'  => 'bank-manager',
                'type'    => 'debt',
                'context' => $context,
                'title'   => $title,
                'message' => $message,
                'url'     => '/admin/bank-manager/debts',
            ]);
        }
    }
}
