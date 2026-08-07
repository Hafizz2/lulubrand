<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoyaltyController extends Controller
{
    public function index()
    {
        $recentTransactions = LoyaltyTransaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'customer_name' => $t->user?->name ?? 'Guest',
                'customer_phone' => $t->user?->phone ?? '',
                'type' => $t->type,
                'points' => $t->points,
                'description' => $t->description,
                'created_at' => $t->created_at->toIso8601String(),
            ]);

        return Inertia::render('Loyalty/Index', [
            'settings' => app(LoyaltyService::class)->getSettings(),
            'recentTransactions' => $recentTransactions,
        ]);
    }
    
    public function search(Request $request)
    {
        $phone = $request->query('phone');
        if (!$phone || strlen($phone) < 4) {
            return response()->json(['customers' => []]);
        }
        
        $customers = User::where('role', 'customer')
            ->where('phone', 'LIKE', "%{$phone}%")
            ->with('loyaltyPoints')
            ->take(10)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'phone' => $u->phone,
                'email' => $u->email,
                'points_balance' => $u->loyaltyPoints?->balance ?? 0,
                'lifetime_earned' => $u->loyaltyPoints?->lifetime_earned ?? 0,
            ]);
        
        return response()->json(['customers' => $customers]);
    }
    
    public function show(User $user)
    {
        $loyaltyService = app(LoyaltyService::class);
        $history = $loyaltyService->getHistory($user, 20);
        $balance = $loyaltyService->getBalance($user);
        $loyaltyPoint = LoyaltyPoint::where('user_id', $user->id)->first();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'points_balance' => $balance,
            'lifetime_earned' => $loyaltyPoint?->lifetime_earned ?? 0,
            'lifetime_redeemed' => $loyaltyPoint?->lifetime_redeemed ?? 0,
            'transactions' => $history->items(),
        ]);
    }
    
    public function award(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'purchase_amount' => 'required|numeric|min:1',
        ]);
        
        $user = User::findOrFail($request->user_id);
        $amountCents = (int) round($request->purchase_amount * 100);
        
        $loyaltyService = app(LoyaltyService::class);
        $transaction = $loyaltyService->earnPoints(
            $user,
            $amountCents,
            'in_shop',
            null,
            'in_shop_purchase',
            Auth::id(),
            "In-store purchase of " . number_format($request->purchase_amount, 2) . " Birr"
        );
        
        if (!$transaction) {
            return back()->with('error', 'Could not award points. Loyalty program may be disabled.');
        }
        
        $this->notifyCustomer($user, $transaction);
        
        return back()->with('success', "Awarded {$transaction->points} points to {$user->name}.");
    }
    
    public function redeem(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
        ]);
        
        $user = User::findOrFail($request->user_id);
        $points = $request->points;
        
        $loyaltyService = app(LoyaltyService::class);
        $balance = $loyaltyService->getBalance($user);
        
        if ($balance < $points) {
            return back()->with('error', 'Insufficient points balance.');
        }
        
        $minRedeem = (int) \App\Models\SystemSetting::get('loyalty_min_redeem', '50');
        if ($points < $minRedeem) {
            return back()->with('error', "Minimum points to redeem is {$minRedeem}.");
        }
        
        $discountCents = $loyaltyService->redeemPoints($user, $points);
        
        if ($discountCents <= 0) {
            return back()->with('error', 'Could not redeem points. Verification failed.');
        }
        
        $discountBirr = $discountCents / 100;
        
        // Notify customer via Telegram if linked
        if ($user->telegram_chat_id) {
            $newBalance = $loyaltyService->getBalance($user);
            $message = "💰 <b>Points Redeemed in Shop!</b>\n\n"
                . "You redeemed <b>{$points} points</b> for a <b>{$discountBirr} Birr discount</b>!\n\n"
                . "💰 Your new balance: <b>{$newBalance} points</b>";
            
            \App\Jobs\SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
        }

        // Dispatch SMS notification via AfroMessage
        if ($user->phone) {
            $newBalance = $loyaltyService->getBalance($user);
            $smsMessage = "Hey {$user->name}, thank you for shopping with LULU! You used {$points} LULU points, and your total points are {$newBalance} points. See you again soon.";
            \App\Jobs\SendSmsNotification::dispatch($user->phone, $smsMessage);
        }

        // Dispatch Web Push notification
        $newBalance = $loyaltyService->getBalance($user);
        $pushTitle = "💰 Redeemed {$points} LULU Points!";
        $pushBody  = "You redeemed points for a {$discountBirr} Birr discount! Remaining balance: {$newBalance} points.";
        \App\Jobs\SendWebPushNotification::dispatch($user->id, $pushTitle, $pushBody, url('/cart'));
        
        return back()->with('success', "Successfully redeemed {$points} points for {$discountBirr} Birr discount!");
    }
    
    private function notifyCustomer(User $user, LoyaltyTransaction $transaction): void
    {
        $balance = app(LoyaltyService::class)->getBalance($user);

        if ($user->telegram_chat_id) {
            $message = "🎉 <b>Points Earned!</b>\n\n"
                . "You earned <b>{$transaction->points} points</b> from your in-store purchase!\n"
                . "Purchase: " . number_format($transaction->purchase_amount_cents / 100, 2) . " Birr\n\n"
                . "💰 Your balance: <b>{$balance} points</b>";
            
            \App\Jobs\SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
        }

        if ($user->phone) {
            $smsMessage = "Hey {$user->name}, thank you for your purchase from LULU! You got {$transaction->points} LULU points, and your total points are {$balance} points. See you again soon.";
            \App\Jobs\SendSmsNotification::dispatch($user->phone, $smsMessage);
        }

        // Dispatch Web Push notification
        $pushTitle = "🎉 Earned +{$transaction->points} LULU Points!";
        $pushBody  = "You earned points from your in-store purchase! Current balance: {$balance} points.";
        \App\Jobs\SendWebPushNotification::dispatch($user->id, $pushTitle, $pushBody, url('/cart'));
    }
}
