<?php

use Illuminate\Support\Facades\Route;

Route::get('/push/subscribe', [PushNotificationController::class, 'subscribePage'])
        ->name('push.subscribe.page');
// Save browser subscription (AJAX)
Route::post('/push/subscribe', [PushNotificationController::class, 'subscribe'])
->name('push.subscribe');
Route::middleware(['auth'])->group(function () {
    // Admin: list subscribers
    Route::get('/admin/push', [PushNotificationController::class, 'index'])
        ->name('push.index');

    // Admin: send notification form (per user)
    Route::get('/admin/push/send/{user}', [PushNotificationController::class, 'sendForm'])
        ->name('push.send.form');

    // Admin: send notification action
    Route::post('/admin/push/send', [PushNotificationController::class, 'send'])
        ->name('push.send');
});

Route::get('/enable-subscription', [PushNotificationController::class, 'enableSubscription'])->name('enable-subscription');
Route::get('/disable-subscription', [PushNotificationController::class, 'disableSubscription'])->name('disable-subscription');
Route::get('/send-push-notification', [PushNotificationController::class, 'sendPushNotification'])->name('send-push-notification');
Route::post('/save-subscription', [PushNotificationController::class, 'saveSubscription'])->name('save-subscription');