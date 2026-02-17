<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushNotifications\PushSubscription;
use App\Models\User;
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
        return view('PushNotifications.subscribe');
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

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id'    => "1",
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

        return view('PushNotifications.index', compact('subscriptions'));
    }

    /**
     * Admin: show send notification form
     */
    public function sendForm(User $user)
    {
        return view('PushNotifications.send', compact('user'));
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
                'subject'    => 'mailto:shashikant@hcbspro.com',
                'publicKey'  => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
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
                echo "<pre>";
                print_r($report);
                exit();
                PushSubscription::where('endpoint', $report->getRequest()->getUri())->delete();
            }
        }

        return back()->with('success', 'Notification sent successfully.');
    }
}
