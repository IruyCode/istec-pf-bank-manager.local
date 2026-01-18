<?php

namespace App\Modules\BankManager\Models\FixedExpenses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

use App\Modules\BankManager\Models\OperationSubCategory;

class FixedExpense extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_fixed_expenses';

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'is_variable_amount',
        'due_day',
        'operation_sub_category_id',
        'last_generated_at',
        'is_active',
    ];

    protected $casts = [
        'is_variable_amount' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Subcategoria associada à despesa fixa.
     */
    public function subCategory()
    {
        return $this->belongsTo(OperationSubCategory::class, 'operation_sub_category_id');
    }

    /**
     * Pagamentos registrados desta despesa fixa.
     */
    public function payments()
    {
        return $this->hasMany(FixedExpensePayment::class, 'fixed_expense_id');
    }

    /**
     * Converte o due_day em uma data válida, ajustando o dia 31 para o último dia do mês.
     */
    public function resolveDueDate(int $year, int $month): Carbon
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $day = min($this->due_day, $daysInMonth);

        return Carbon::createFromDate($year, $month, $day);
    }

    /**
     * Retorna o status: 'paga', 'atrasada' ou 'em aberto'
     */
    public function getStatusForMonth(int $year, int $month): string
    {
        // Já existe pagamento registrado?
        $payment = $this->payments()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($payment) {
            return 'paga';
        }

        $dueDate = $this->resolveDueDate($year, $month);

        if (now()->greaterThan($dueDate)) {
            return 'atrasada';
        }

        return 'em aberto';
    }

    /**
     * Retorna a média dos últimos N pagamentos (para despesas variáveis)
     */
    public function getAverageAmount(int $lastMonths = 6): float
    {
        $payments = $this->payments()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit($lastMonths)
            ->get();

        if ($payments->isEmpty()) {
            return $this->amount;
        }

        return round($payments->avg('amount_paid'), 2);
    }

    /**
     * Retorna o valor sugerido (fixo ou média dos últimos pagamentos)
     */
    public function getSuggestedAmount(): float
    {
        if (!$this->is_variable_amount) {
            return $this->amount;
        }

        return $this->getAverageAmount();
    }
}
