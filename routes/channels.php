<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("notification.{userId}", function (User $authUser, int $userId) {
    return $authUser->id === $userId;
});

Broadcast::channel("chat.{chat}", function (User $authUser, Chat $chat) {
    return $authUser->id === $chat->user_id;
});
