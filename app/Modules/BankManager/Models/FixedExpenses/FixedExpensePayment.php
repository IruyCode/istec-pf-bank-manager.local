<?php

namespace App\Modules\BankManager\Models\FixedExpenses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedExpensePayment extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_fixed_expense_payments';

    protected $fillable = [
        'fixed_expense_id',
        'paid_at',
        'year',
        'month',
        'amount_paid',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
    ];

    public function fixedExpense()
    {
        return $this->belongsTo(FixedExpense::class, 'fixed_expense_id');
    }
}
