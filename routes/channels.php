<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('dns.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
