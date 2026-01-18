<?php

namespace App\Modules\Notifications;

class NotificationTriggerRegistry
{
    public static function all(): array
    {
        return [
            // === BANKMANAGER ===
            // \App\Modules\BankManager\Notifications\Triggers\AbnormalExpenseTrigger::class,
            // \App\Modules\BankManager\Notifications\Triggers\CheckRecentTransactionsTrigger::class,
            \App\Modules\BankManager\Notifications\Triggers\DailyInvestmentUpdateTrigger::class,
            // \App\Modules\BankManager\Notifications\Triggers\MonthlySpendingAlertTrigger::class,
            \App\Modules\BankManager\Notifications\Triggers\ReminderDebtInstallmentsTrigger::class,
            \App\Modules\BankManager\Notifications\Triggers\ReminderDebtorsTrigger::class,
            \App\Modules\BankManager\Notifications\Triggers\ReminderFixedExpensesTrigger::class,
            \App\Modules\BankManager\Notifications\Triggers\ReminderGoalsMonthlyTrigger::class,
        ];
    }
}
