<!DOCTYPE html>
<html>
<head>
    <title>Enable Notifications</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

<button type="button" class="btn btn-primary" style="margin-top: 40%; margin-left: 50%; transform: translateX(-50%);" id="enablePush">Enable Notifications</button>
<div id="status" style="display:none;color:green;">Subscribed Successfully ✅</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script type="text/javascript">
var vapidPublicKey = "{{ config('chronos.vapid_public_key') }}";

function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; i++) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

$(document).ready(function () {
    console.log('READY');
    $('#enablePush').on('click', function () {
        if (isIOS()) {
            subscribeIOS();
        } else {
            subscribeAndroid();
        }
    });
});
    function isIOS() {
        return /iphone|ipad|ipod/i.test(navigator.userAgent);
    }

    function isStandalone() {
        return window.navigator.standalone === true;
    }

    function subscribeIOS() {
        if (!isStandalone()) {
            alert('On iPhone: Add this site to Home Screen first');
            return;
        }

        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(function (registration) {

                Notification.requestPermission(function (permission) {

                    if (permission !== 'granted') {
                        alert('Permission denied');
                        return;
                    }

                    registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                    }).then(function (subscription) {

                        function ab2b64(buf) {
                            return btoa(String.fromCharCode.apply(null, new Uint8Array(buf)));
                        }

                        sendToServer({
                            endpoint: subscription.endpoint,
                            p256dh: ab2b64(subscription.getKey('p256dh')),
                            auth: ab2b64(subscription.getKey('auth')),
                            browser: 'safari',
                            device: 'ios'
                        });

                    });

                });

            });
    }

    function subscribeAndroid() {

        navigator.serviceWorker.register('/sw.js')
            .then(function (registration) {

                Notification.requestPermission(function (permission) {

                    if (permission !== 'granted') {
                        alert('Permission denied');
                        return;
                    }

                    registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                    }).then(function (subscription) {

                        function ab2b64(buf) {
                            return btoa(String.fromCharCode.apply(null, new Uint8Array(buf)));
                        }

                        sendToServer({
                            endpoint: subscription.endpoint,
                            p256dh: ab2b64(subscription.getKey('p256dh')),
                            auth: ab2b64(subscription.getKey('auth')),
                            browser: 'chrome',
                            device: 'android'
                        });

                    });

                });

            });
    }

    function sendToServer(payload) {
        $.ajax({
            url: "{{ route('push.subscribe') }}",
            type: "POST",
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(payload),
            success: function () {
                alert('Subscribed successfully ✅');
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Failed to save subscription');
            }
        });

    }
</script>

</body>
</html>
