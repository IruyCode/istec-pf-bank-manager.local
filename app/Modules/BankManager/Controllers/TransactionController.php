<?php

namespace App\Modules\BankManager\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\BankManager\Models\Transaction;
use App\Modules\BankManager\Models\AccountBalance;
use App\Modules\BankManager\Models\OperationType;
use App\Modules\BankManager\Models\SpendingContext;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'account_balance_id'        => 'required|exists:app_bank_manager_account_balances,id',
            'operation_sub_category_id' => 'required|exists:app_bank_manager_operation_sub_categories,id',
            'operation_type_id'         => 'required|exists:app_bank_manager_operation_types,id',
            'amount'                    => 'required|numeric|min:0.01',
            'description'               => 'nullable|string',
            'performed_at'              => 'nullable|date',
        ]);

        // Validar que a subcategoria pertence ao tipo selecionado
        $subCategory = \App\Modules\BankManager\Models\OperationSubCategory::findOrFail($request->operation_sub_category_id);
        if ($subCategory->operation_type_id != $request->operation_type_id) {
            return back()->with('error', 'A subcategoria selecionada não pertence ao tipo de operação escolhido.');
        }

        $userId = Auth::id();

        // 1. Verifica se existe contexto ativo
        $activeContext = SpendingContext::where('user_id', $userId)
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        $operationType = OperationType::findOrFail($request->operation_type_id);

        $performedAt = $request->filled('performed_at')
            ? Carbon::parse($request->performed_at)
            : now();

        // 2. Cria a transação
        $transaction = Transaction::create([
            'user_id'                   => $userId,
            'account_balance_id'        => $request->account_balance_id,
            'operation_sub_category_id' => $request->operation_sub_category_id,
            'operation_type_id'         => $operationType->id,
            'amount'                    => $request->amount,
            'spending_context_id'       => $activeContext?->id,
            'transaction_date'          => $performedAt,
        ]);


        // 3. Atualiza saldo
        $balance = AccountBalance::where('user_id', $userId)
            ->findOrFail($request->account_balance_id);

        if ($operationType->operation_type === 'income') {
            $balance->current_balance += $request->amount;
        } else {
            $balance->current_balance -= $request->amount;
        }

        $balance->save();

        return back()->with('success', 'Transação registrada com sucesso!');
    }
}
