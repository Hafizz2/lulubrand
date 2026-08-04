<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe or update a push subscription endpoint.
     * Stores VAPID subscription data keyed by session or user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'                  => 'required|url|max:2000',
            'keys.p256dh'               => 'required|string',
            'keys.auth'                 => 'required|string',
            'content_encoding'          => 'nullable|string|max:20',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'user_id'          => Auth::id(),
                'session_id'       => session()->getId(),
                'public_key'       => $request->input('keys.p256dh'),
                'auth_token'       => $request->input('keys.auth'),
                'content_encoding' => $request->input('content_encoding', 'aesgcm'),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Subscribed to push notifications.']);
    }

    /**
     * Unsubscribe a push endpoint (e.g. user revokes permission).
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|url|max:2000']);

        PushSubscription::where('endpoint', $request->input('endpoint'))->delete();

        return response()->json(['success' => true, 'message' => 'Unsubscribed from push notifications.']);
    }
}
