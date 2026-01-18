<?php

namespace App\Modules\BankManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SpendingContext extends Model
{
    use HasFactory;

    protected $table = 'app_bank_manager_spending_contexts';
    
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'budget',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'budget'     => 'decimal:2',
        'is_active'  => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'spending_context_id');
    }
}
