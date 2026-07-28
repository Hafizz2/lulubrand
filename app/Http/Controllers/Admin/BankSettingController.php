<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBankAccountRequest;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankSettingController extends Controller
{
    public function index(): Response
    {
        $banks = BankAccount::orderBy('sort_order', 'asc')->get();

        return Inertia::render('Settings/Banks', [
            'banks' => $banks,
        ]);
    }

    public function store(StoreBankAccountRequest $request)
    {
        $validated = $request->validated();
        $maxOrder = BankAccount::max('sort_order') ?? 0;

        BankAccount::create([
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'logo_url' => $validated['logo_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
        ]);

        return back()->with('success', "Bank account {$validated['bank_name']} added!");
    }

    public function update(StoreBankAccountRequest $request, BankAccount $bank)
    {
        $validated = $request->validated();
        $bank->update($validated);

        return back()->with('success', 'Bank account updated successfully!');
    }

    public function toggle(BankAccount $bank)
    {
        $bank->update(['is_active' => ! $bank->is_active]);

        return back()->with('success', 'Bank account status toggled.');
    }

    public function destroy(BankAccount $bank)
    {
        $bank->delete();

        return back()->with('success', 'Bank account deleted.');
    }
}
