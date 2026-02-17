<?php

return [

    'vapid_public_key' => env('VAPID_PUBLIC_KEY'),

    'vapid_private_key' => env('VAPID_PRIVATE_KEY'),

    'vapid_subject' => env('VAPID_SUBJECT', 'mailto:example@example.com'),

    'default_user_model' => \App\Models\User::class,

];