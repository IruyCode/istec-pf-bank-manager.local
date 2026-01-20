<?php

namespace App\Modules\BankManager\Models\Debtors;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debtor extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_debtors';

    protected $fillable = ['user_id', 'name', 'description', 'amount', 'due_date', 'is_paid', 'paid_at', 'transaction_id'];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Relacionamento com a transação quando o pagamento é recebido
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Modules\BankManager\Models\Transaction::class, 'transaction_id');
    }

    /**
     * Histórico de edições do devedor
     */
    public function edits()
    {
        return $this->hasMany(DebtorEdit::class, 'debtor_id');
    }

    // Quando um devedor é marcado como pago, remove o histórico de edições
    protected static function booted()
    {
        static::updated(function ($debtor) {
            if ($debtor->is_paid) {
                DebtorEdit::where('debtor_id', $debtor->id)->delete();
            }
        });
    }
}
