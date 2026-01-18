<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\BankManager\Services\Notifications\ExpenseService;
use App\Modules\BankManager\Services\Notifications\InvestmentService;
use App\Modules\BankManager\Services\Notifications\DebtorService;
use App\Modules\BankManager\Services\Notifications\DebtService;
use App\Modules\BankManager\Services\Notifications\GoalService;
use App\Modules\BankManager\Services\Notifications\SpendingAlertService;
use Illuminate\Support\Facades\Log;

class CheckExpensesCommand extends Command
{
    /**
     * Nome e assinatura do comando.
     */
    protected $signature = 'bankmanager:check-expenses';

    /**
     * Descrição do comando.
     */
    protected $description = 'Verifica despesas, investimentos, dívidas e metas para enviar notificações aos usuários';

    /**
     * Executa o comando.
     */
    public function handle(
        ExpenseService $expenseService,
        InvestmentService $investmentService,
        DebtorService $debtorService,
        DebtService $debtService,
        GoalService $goalService,
        SpendingAlertService $spendingAlertService
    ): int
    {
        $this->info('🚀 Iniciando verificação de notificações do Bank Manager...');
        Log::info('CheckExpensesCommand started');

        $startTime = now();
        $usersProcessed = 0;
        $notificationsCreated = 0;

        // Processa todos os usuários
        $users = User::all();

        foreach ($users as $user) {
            try {
                $this->info("📊 Processando usuário: {$user->name} (ID: {$user->id})");

                // 1️⃣ Despesas Recentes
                $this->line('  ├─ Verificando despesas recentes...');
                $expenseService->checkRecentExpenses($user->id);

                // 2️⃣ Despesas Fixas Próximas
                $this->line('  ├─ Verificando despesas fixas próximas...');
                $expenseService->checkUpcomingFixedExpenses($user->id);

                // 3️⃣ Investimentos
                $this->line('  ├─ Verificando investimentos...');
                $investmentService->remindDailyUpdate($user->id);

                // 4️⃣ Devedores
                $this->line('  ├─ Verificando devedores...');
                $debtorService->remindDueDebtors($user->id);

                // 5️⃣ Dívidas (Parcelas)
                $this->line('  ├─ Verificando parcelas de dívidas...');
                $debtService->remindDueInstallments($user->id);

                // 6️⃣ Metas Financeiras
                $this->line('  ├─ Verificando metas financeiras...');
                $goalService->remindMonthlyContributions($user->id);

                // 7️⃣ Alertas de Gastos
                $this->line('  └─ Verificando alertas de gastos...');
                $spendingAlertService->checkSpendingAlerts($user->id);

                $usersProcessed++;
                $this->info("✅ Usuário {$user->name} processado com sucesso!");

            } catch (\Exception $e) {
                $this->error("❌ Erro ao processar usuário {$user->name}: " . $e->getMessage());
                Log::error("Error processing user {$user->id}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        $duration = now()->diffInSeconds($startTime);

        $this->newLine();
        $this->info('✨ Verificação concluída!');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Usuários processados', $usersProcessed],
                ['Total de usuários', $users->count()],
                ['Tempo de execução', "{$duration}s"],
                ['Horário', now()->format('d/m/Y H:i:s')],
            ]
        );

        Log::info('CheckExpensesCommand completed', [
            'users_processed' => $usersProcessed,
            'duration_seconds' => $duration,
        ]);

        return Command::SUCCESS;
    }
}
