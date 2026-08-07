<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWebPushNotification;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PushBroadcastController extends Controller
{
    public function index(): Response
    {
        $totalSubscribers = PushSubscription::count();
        $registeredSubscribers = PushSubscription::whereNotNull('user_id')->count();
        $anonymousSubscribers = PushSubscription::whereNull('user_id')->count();

        return Inertia::render('Notifications/PushBroadcast', [
            'stats' => [
                'total'      => $totalSubscribers,
                'registered' => $registeredSubscribers,
                'anonymous'  => $anonymousSubscribers,
            ],
        ]);
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,customers',
            'title'  => 'required|string|max:255',
            'body'   => 'required|string|max:1000',
            'url'    => 'nullable|url|max:2000',
        ]);

        $targetMode = $validated['target'] === 'all' ? 'broadcast_all' : 'broadcast_customers';

        SendWebPushNotification::dispatch(
            null,
            $validated['title'],
            $validated['body'],
            $validated['url'] ?? url('/'),
            asset('logo.png'),
            $targetMode
        );

        return back()->with('success', "Push notification broadcast queued for target '{$validated['target']}' subscribers!");
    }
}
