# Quick Start Guide - WAMP Server Testing

## Prerequisites Checklist
- ✅ WAMP Server running
- ✅ Laravel application in `C:\wamp64\www\your-laravel-app`
- ✅ Composer installed
- ✅ PHP 7.4+ enabled in WAMP

## Quick Setup (5 Minutes)

### 1. Install Package in Your Laravel App

Navigate to your Laravel application directory:
```powershell
cd C:\wamp64\www\your-laravel-app
```

Add to `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../chronos-ai-notify-package"
        }
    ],
    "require": {
        "iamsp007/chronos-ai-notify": "*"
    }
}
```

Install:
```powershell
composer update iamsp007/chronos-ai-notify
```

### 2. Publish Files

```powershell
php artisan vendor:publish --tag=chronos-config
php artisan vendor:publish --tag=chronos-assets
```

### 3. Run Migration

```powershell
php artisan migrate
```

### 4. Generate VAPID Keys

Create `generate-keys.php` in your Laravel root:
```php
<?php
require 'vendor/autoload.php';
use Minishlink\WebPush\VAPID;
$keys = VAPID::createVapidKeys();
echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
echo "VAPID_SUBJECT=mailto:your-email@example.com\n";
```

Run:
```powershell
php generate-keys.php
```

Copy the output to your `.env` file.

### 5. Setup Service Worker

Copy service worker to public root:
```powershell
copy vendor\chronos\PushNotifications\sw.js public\sw.js
```

Or add this route to `routes/web.php`:
```php
Route::get('/sw.js', function () {
    return response()->file(public_path('vendor/chronos/PushNotifications/sw.js'), [
        'Content-Type' => 'application/javascript'
    ]);
});
```

### 6. Enable HTTPS (Required!)

**Easiest Option - Use ngrok:**
```powershell
# Download ngrok from https://ngrok.com
ngrok http 80
# Use the HTTPS URL provided (e.g., https://abc123.ngrok.io)
```

**Or use Laravel's built-in HTTPS** (Add to `AppServiceProvider.php`):
```php
use Illuminate\Support\Facades\URL;

public function boot()
{
    if (app()->environment('local')) {
        URL::forceScheme('https');
    }
}
```

### 7. Test!

1. Visit: `https://your-domain/push/subscribe`
2. Click "Enable Notifications"
3. Allow permission
4. Check database: `SELECT * FROM push_subscriptions;`
5. Visit: `https://your-domain/admin/push` (login required)
6. Send a test notification!

## Common Issues

**Routes not working?**
```powershell
php artisan route:clear
php artisan config:clear
```

**Service worker 404?**
- Ensure `sw.js` is in `public/sw.js` or route is configured

**Permission denied?**
- Must use HTTPS (localhost works without HTTPS in Chrome)

**Package not found?**
```powershell
composer dump-autoload
```

## Test URLs

- Subscribe: `/push/subscribe`
- Admin List: `/admin/push` (auth required)
- Send Form: `/admin/push/send/{user_id}` (auth required)

## Need Help?

See `TESTING_GUIDE.md` for detailed instructions.
