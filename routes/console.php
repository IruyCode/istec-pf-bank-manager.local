<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🔔 NOTIFICAÇÕES DO BANK MANAGER
// Executa diariamente às 09:00 (timezone Lisboa)
// Verifica 7 áreas: despesas recentes, despesas fixas, investimentos, 
// devedores, dívidas, metas e alertas de gastos
Schedule::command('bankmanager:check-expenses')
    ->dailyAt('09:00')
    ->timezone('Europe/Lisbon')
    ->withoutOverlapping();

// 🔔 NOTIFICAÇÕES DO TASK MANAGER
// Executa diariamente às 08:00 (timezone Lisboa)
// Verifica: tarefas do dia, eventos próximos, aniversários e hábitos
Schedule::command('taskmanager:check-reminders')
    ->dailyAt('08:00')
    ->timezone('Europe/Lisbon')
    ->withoutOverlapping();

// 🔔 NOTIFICAÇÕES DE HÁBITOS - VERIFICAÇÃO A CADA 5 MINUTOS
// Para notificar 2h e 30min antes do fim de cada período (manhã, tarde, noite)
Schedule::command('taskmanager:check-reminders')
    ->everyFiveMinutes()
    ->timezone('Europe/Lisbon')
    ->withoutOverlapping();
