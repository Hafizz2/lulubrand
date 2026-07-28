<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->with(['orders' => fn($q) => $q->latest()->take(1)])
            ->latest()
            ->paginate(25);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    public function show(User $user)
    {
        $user->load(['orders.items.product']);

        return Inertia::render('Customers/Show', [
            'customer' => $user,
        ]);
    }
}
