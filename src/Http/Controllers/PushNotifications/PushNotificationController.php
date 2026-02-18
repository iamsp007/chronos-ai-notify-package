<?php

namespace Iamsp007\ChronosAiNotify\Http\Controllers\PushNotifications;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iamsp007\ChronosAiNotify\Models\PushNotifications\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationController extends Controller
{
    /**
     * Show subscription page (user side)
     */
    public function subscribePage()
    {
        // dd('subscribePage');
        return view('chronos::PushNotifications.subscribe');
    }

    /**
     * Save / update browser push subscription
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
            'p256dh'   => 'required',
            'auth'     => 'required',
        ]);

        $userModel = config('chronos.default_user_model', \App\Models\User::class);
        $userId = auth()->check() ? auth()->id() : ($request->user_id ?? null);
        
        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id'    => $userId,
                'p256dh'     => $request->p256dh,
                'auth'       => $request->auth,
                'browser'    => $request->browser ?? 'chrome',
                'device'     => $request->device ?? 'android',
                'ip_address' => $request->ip(),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Admin: list all push subscribers
     */
    public function index()
    {
        $subscriptions = PushSubscription::with('user')
            ->latest()
            ->get();

        return view('chronos::PushNotifications.index', compact('subscriptions'));
    }

    /**
     * Admin: show send notification form
     */
    public function sendForm($user)
    {
        $userModel = config('chronos.default_user_model', \App\Models\User::class);
        $user = $userModel::findOrFail($user);
        return view('chronos::PushNotifications.send', compact('user'));
    }

    /**
     * Admin: send notification to particular user
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'url'     => 'nullable|url',
        ]);

        $subscriptions = PushSubscription::where('user_id', $request->user_id)->get();

        if ($subscriptions->isEmpty()) {
            return back()->with('error', 'No active subscriptions for this user.');
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject'    => config('chronos.vapid_subject'),
                'publicKey'  => config('chronos.vapid_public_key'),
                'privateKey' => config('chronos.vapid_private_key'),
            ],
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->p256dh,
                    'auth'   => $sub->auth,
                ],
            ]);

            $payload = json_encode([
                'title' => $request->title,
                'body'  => $request->body,
                'url'   => $request->url ?? url('/'),
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        // Send & clean expired subscriptions
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // Delete expired/invalid subscriptions
                PushSubscription::where('endpoint', $report->getRequest()->getUri())->delete();
            }
        }

        return back()->with('success', 'Notification sent successfully.');
    }

    /**
     * Enable subscription page
     */
    public function enableSubscription()
    {
        return view('chronos::PushNotifications.enable-subscription');
    }

    /**
     * Disable subscription
     */
    public function disableSubscription()
    {
        // Implementation for disabling subscription
        return back()->with('success', 'Subscription disabled successfully.');
    }

    /**
     * Send push notification (alternative endpoint)
     */
    public function sendPushNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string',
            'body'    => 'required|string',
            'url'     => 'nullable|url',
        ]);

        return $this->send($request);
    }

    /**
     * Save subscription (alternative endpoint)
     */
    public function saveSubscription(Request $request)
    {
        return $this->subscribe($request);
    }
}
