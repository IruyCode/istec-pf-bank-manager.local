<?php

namespace App\Modules\BankManager\Models\Debts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_debts';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'total_amount',
        'installments',
        'start_date',
        'status',
        'fixed_expense_id',
    ];

    // No model AppBankManagerDebt:
    public function installmentsList()
    {
        return $this->hasMany(DebtInstallment::class, 'debt_id');
    }


    public function paidInstallments()
    {
        return $this->installments()->whereNotNull('paid_at');
    }

    public function remainingInstallments()
    {
        return $this->installments()->whereNull('paid_at');
    }

    /**
     * Relacionamento com a despesa fixa associada
     */
    public function fixedExpense()
    {
        return $this->belongsTo(\App\Modules\BankManager\Models\FixedExpenses\FixedExpense::class, 'fixed_expense_id');
    }

    /**
     * Cria uma despesa fixa automaticamente quando a dívida é ativada
     */
    public function createFixedExpense()
    {
        // Verifica se já existe uma despesa fixa
        if ($this->fixed_expense_id) {
            return $this->fixedExpense;
        }

        // Calcula o valor da parcela
        $installmentAmount = $this->total_amount / $this->installments;

        // Cria a despesa fixa
        $fixedExpense = \App\Modules\BankManager\Models\FixedExpenses\FixedExpense::create([
            'user_id' => $this->user_id,
            'name' => "Parcela - {$this->name}",
            'amount' => $installmentAmount,
            'due_day' => now()->day, // Usa o dia atual como vencimento
            'is_active' => true,
        ]);

        // Associa a despesa fixa à dívida
        $this->update(['fixed_expense_id' => $fixedExpense->id]);

        return $fixedExpense;
    }

    /**
     * Remove a despesa fixa quando a dívida é pausada ou concluída
     */
    public function removeFixedExpense()
    {
        if ($this->fixed_expense_id) {
            $fixedExpense = $this->fixedExpense;
            
            // Remove a associação
            $this->update(['fixed_expense_id' => null]);
            
            // Deleta a despesa fixa
            if ($fixedExpense) {
                $fixedExpense->delete();
            }
        }
    }
}
