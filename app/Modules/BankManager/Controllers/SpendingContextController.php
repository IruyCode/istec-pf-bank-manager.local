<?php

namespace App\Modules\BankManager\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\BankManager\Models\SpendingContext;
use Illuminate\Support\Facades\Auth;

class SpendingContextController extends Controller
{
    public function index()
    {
        $contexts = SpendingContext::where('user_id', Auth::id())
            ->orderByDesc('start_date')
            ->get();

        return view('bankmanager::spending-contexts.index', compact('contexts'));
    }

    public function create()
    {
        return view('bankmanager::spending-contexts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:230',
            'type'       => 'required|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'budget'     => 'nullable|numeric|min:0',
        ]);

        SpendingContext::create([
            ...$request->all(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('bank-manager.spending-contexts.index')
            ->with('success', 'Contexto criado com sucesso!');
    }

    public function show(SpendingContext $context)
    {
       $spendingContext = $context->load('transactions.operationSubCategory.operationCategory');

        return view('bankmanager::spending-contexts.show', compact('spendingContext'));
    }

    public function edit(SpendingContext $context)
    {
        return view('bankmanager::spending-contexts.edit', compact('context'));
    }

    public function update(Request $request, SpendingContext $context)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'budget'      => 'nullable|numeric|min:0',
            'is_active'   => 'required|boolean'
        ]);

        $context->update($validated);

        return redirect()
            ->route('bank-manager.spending-contexts.show', $context)
            ->with('success', 'Contexto atualizado com sucesso!');
    }

    public function destroy(SpendingContext $context)
    {
        $context->delete();

        return redirect()->route('bank-manager.spending-contexts.index')
            ->with('success', 'Contexto removido!');
    }
}
