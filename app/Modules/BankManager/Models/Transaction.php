<?php

namespace App\Modules\BankManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_transactions';

    protected $fillable = ['user_id', 'operation_sub_category_id', 'account_balance_id', 'operation_type_id', 'description', 'amount', 'spending_context_id', 'transaction_date', 'is_recurring', 'due_day', 'debt_installment_id'];

    protected $casts = [
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function operationSubCategory()
    {
        return $this->belongsTo(OperationSubCategory::class, 'operation_sub_category_id');
    }

    public function accountBalance()
    {
        return $this->belongsTo(AccountBalance::class, 'account_balance_id');
    }

    public function operationType()
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id');
    }

    public function spendingContext()
    {
        return $this->belongsTo(SpendingContext::class, 'spending_context_id');
    }
}
