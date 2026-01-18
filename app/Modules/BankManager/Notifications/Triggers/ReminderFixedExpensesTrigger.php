<?php

namespace App\Modules\BankManager\Notifications\Triggers;

use App\Modules\Notifications\Services\NotificationService;
use App\Modules\BankManager\Models\FixedExpenses\FixedExpense;

use App\Modules\Notifications\Models\CoreNotification;

use Carbon\Carbon;

class ReminderFixedExpensesTrigger implements TriggerInterface
{
    public static function label(): string
    {
        return 'fixed_expenses';
    }

    public function shouldTrigger(): bool
    {
        return true; // Lógica interna decide se dispara
    }

    public function run(NotificationService $service): void
    {
        $tz = config('app.timezone', 'Europe/Lisbon');
        $today = Carbon::now($tz)->startOfDay();

        $expenses = FixedExpense::all();

        foreach ($expenses as $expense) {

            // Evita datas inválidas (ex: dia 30 em fevereiro)
            if ($expense->due_day > $today->endOfMonth()->day) {
                continue;
            }

            $dueDate = Carbon::createFromDate(
                $today->year,
                $today->month,
                $expense->due_day,
                $tz
            )->startOfDay();

            $daysUntilDue = $today->diffInDays($dueDate, false);

            // Só notifica nesses dias
            if (!in_array($daysUntilDue, [10, 5, 1, 0], true)) {
                continue;
            }

            // Label (D10, D5, D1, today)
            $label = match ($daysUntilDue) {
                10 => 'D10',
                5  => 'D5',
                1  => 'D1',
                0  => 'today',
            };

            // Evitar duplicação
            $context = "fixed_expense_{$expense->id}_{$label}";

            $exists = CoreNotification::where('context', $context)
                ->whereDate('triggered_at', $today->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            // Mensagem formatada
            $amount = '€' . number_format($expense->amount, 2, ',', '.');
            $dueFormatted = $dueDate->format('d/m');

            $title = match ($label) {
                'D10' => "💸 Despesa em 10 dias: {$expense->name}",
                'D5'  => "⚠️ Despesa em 5 dias: {$expense->name}",
                'D1'  => "🔔 Vence amanhã: {$expense->name}",
                'today' => "📅 Vencimento HOJE: {$expense->name}",
            };

            $message = "A despesa '{$expense->name}' vence em {$dueFormatted}. Valor: {$amount}.";

            // Criar notificação
            $service->notify([
                'module'  => 'bank-manager',
                'type'    => 'fixed_expense',
                'context' => $context,
                'title'   => $title,
                'message' => $message,
                'url'     => '/admin/bank-manager/transactions',
            ]);
        }
    }
}
