# Testing Guide - Chronos AI Notify Package

## Prerequisites

1. **WAMP Server** installed and running
2. **Laravel Application** (version 8.0+)
3. **Composer** installed
4. **HTTPS enabled** (required for Push Notifications - use `localhost` or configure SSL)

## Step 1: Install Package in Your Laravel Application

### Option A: Install as Local Package (Development)

1. In your Laravel application's `composer.json`, add:

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

2. Run composer update:
```bash
composer update iamsp007/chronos-ai-notify
```

### Option B: Install via Packagist (Production)

```bash
composer require iamsp007/chronos-ai-notify
```

## Step 2: Publish Configuration and Assets

```bash
# Publish config file
php artisan vendor:publish --tag=chronos-config

# Publish service worker (sw.js)
php artisan vendor:publish --tag=chronos-assets
```

This will:
- Copy `config/chronos.php` to your app's config directory
- Copy `resources/js/PushNotifications/sw.js` to `public/vendor/chronos/PushNotifications/sw.js`

## Step 3: Run Migrations

```bash
php artisan migrate
```

This will create the `push_subscriptions` table.

## Step 4: Generate VAPID Keys

Push notifications require VAPID keys. Generate them using:

```bash
# Install web-push globally (if not already installed)
npm install -g web-push

# Generate VAPID keys
web-push generate-vapid-keys
```

Or use this PHP script:

```php
<?php
// generate-vapid-keys.php
require 'vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$keys = VAPID::createVapidKeys();
echo "Public Key: " . $keys['publicKey'] . "\n";
echo "Private Key: " . $keys['privateKey'] . "\n";
```

Run: `php generate-vapid-keys.php`

## Step 5: Configure Environment Variables

Add to your `.env` file:

```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:your-email@example.com
```

## Step 6: Configure Service Worker Path

The service worker (`sw.js`) needs to be accessible at the root of your domain.

**Option 1: Copy manually**
```bash
# Copy sw.js to public directory
copy vendor/chronos/PushNotifications/sw.js public/sw.js
```

**Option 2: Create symlink**
```bash
# Windows (Run as Administrator)
mklink public\sw.js vendor\chronos\PushNotifications\sw.js
```

**Option 3: Add route to serve service worker** (Add to your `routes/web.php`):
```php
Route::get('/sw.js', function () {
    return response()->file(public_path('vendor/chronos/PushNotifications/sw.js'), [
        'Content-Type' => 'application/javascript'
    ]);
});
```

## Step 7: Enable HTTPS (Required for Push Notifications)

### For Local Development:

**Option 1: Use Laravel Valet** (if available)
```bash
valet secure your-app-name
```

**Option 2: Use WAMP SSL Configuration**
1. Enable SSL module in WAMP
2. Create self-signed certificate
3. Configure virtual host for HTTPS

**Option 3: Use ngrok** (Quick testing)
```bash
ngrok http 80
# Use the HTTPS URL provided
```

**Option 4: Use Laravel's built-in HTTPS** (for development only)
Add to `AppServiceProvider.php`:
```php
public function boot()
{
    if (app()->environment('local')) {
        URL::forceScheme('https');
    }
}
```

## Step 8: Test the Package

### Test Routes:

1. **Subscribe Page** (Public):
   ```
   http://localhost/push/subscribe
   ```
   Or if using HTTPS:
   ```
   https://localhost/push/subscribe
   ```

2. **Admin - List Subscribers** (Requires Auth):
   ```
   http://localhost/admin/push
   ```

3. **Admin - Send Notification Form**:
   ```
   http://localhost/admin/push/send/{user_id}
   ```

### Testing Steps:

1. **Open Subscribe Page**:
   - Navigate to `/push/subscribe`
   - Click "Enable Notifications"
   - Allow notification permission in browser
   - You should see "Subscribed Successfully ✅"

2. **Check Database**:
   ```sql
   SELECT * FROM push_subscriptions;
   ```
   You should see a new record with your subscription details.

3. **Test Admin Panel** (if logged in):
   - Navigate to `/admin/push`
   - You should see your subscription listed
   - Click "Send" to send a test notification

4. **Send Test Notification**:
   - Fill in the form:
     - Title: "Test Notification"
     - Body: "This is a test message"
     - URL: (optional) "https://localhost"
   - Submit the form
   - You should receive a push notification

## Troubleshooting

### Issue: Service Worker Not Found
- **Solution**: Ensure `sw.js` is accessible at `/sw.js` in your public directory

### Issue: "Permission denied" error
- **Solution**: Ensure you're using HTTPS (required for push notifications)

### Issue: VAPID keys not working
- **Solution**: Regenerate VAPID keys and update `.env` file

### Issue: Routes not found
- **Solution**: Clear route cache:
  ```bash
  php artisan route:clear
  php artisan config:clear
  ```

### Issue: Views not found
- **Solution**: Clear view cache:
  ```bash
  php artisan view:clear
  ```

### Issue: Package not loading
- **Solution**: Run composer dump-autoload:
  ```bash
  composer dump-autoload
  ```

## Browser Compatibility

- ✅ Chrome/Edge (Desktop & Android)
- ✅ Firefox (Desktop & Android)
- ✅ Safari (macOS & iOS - requires PWA installation)
- ❌ Internet Explorer (not supported)

## Additional Notes

1. **User Authentication**: The package uses Laravel's default auth. Ensure users table exists and auth is configured.

2. **Service Worker Scope**: The service worker must be at the root (`/sw.js`) to work properly.

3. **Testing on Mobile**: For mobile testing, use ngrok or deploy to a server with HTTPS.

4. **Database**: Ensure your database connection is configured correctly in `.env`.

## Quick Test Checklist

- [ ] Package installed via Composer
- [ ] Config published (`config/chronos.php` exists)
- [ ] Assets published (`public/vendor/chronos/PushNotifications/sw.js` exists)
- [ ] Migration run (`push_subscriptions` table exists)
- [ ] VAPID keys generated and added to `.env`
- [ ] HTTPS enabled (or using localhost)
- [ ] Service worker accessible at `/sw.js`
- [ ] Routes accessible
- [ ] Can subscribe to notifications
- [ ] Can send test notification

## Support

For issues or questions, check:
- Laravel documentation: https://laravel.com/docs
- Web Push API: https://web.dev/push-notifications-overview/
- Minishlink WebPush: https://github.com/web-push-libs/web-push-php
