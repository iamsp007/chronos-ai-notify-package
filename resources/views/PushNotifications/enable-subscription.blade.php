@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Push Notifications</h2>
    <p>Enable notifications to receive updates.</p>
    <button type="button" class="btn btn-primary" id="enable-push-btn">Enable Notifications</button>
    <a href="{{ route('send-push-notification') }}">Send Push Notification</a>
</div>

<script>
async function enablePush() {
    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            console.warn('Notification permission denied');
            return;
        }

        const registration = await navigator.serviceWorker.register('{{ asset('sw.js') }}');
        await registration.update();

        const vapidKey = 'BD-EzOME2JOB6IvaIbGtso8C4rI9qxu_UKRoZ_62iOzEGDgbd7i_c7UuwIdYPpiBvam-eiB4xnWPEPmRG-7IVEg';
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey)
        });

        const response = await fetch('{{ route("save-subscription") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify(subscription)
        });

        if (response.ok) {
            alert('Notifications enabled successfully!');
        } else {
            throw new Error('Failed to save subscription');
        }
    } catch (error) {
        console.error('Enable push error:', error);
        alert('Could not enable notifications. Please try again.');
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('enable-push-btn');
    if (btn) btn.addEventListener('click', enablePush);
});
</script>
@endsection
