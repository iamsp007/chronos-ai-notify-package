<?php

namespace App\Models\PushNotifications;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;

    protected $table = 'push_subscriptions';

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'browser',
        'device',
        'ip_address',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation: subscription belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
