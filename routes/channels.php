<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return !is_null($user);
});

Broadcast::channel('room.{room}', function ($user, $room) {
    return $room->users->contains($user->id);
});
