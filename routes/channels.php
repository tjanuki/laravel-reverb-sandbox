<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return !is_null($user);
});

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    try {
        $room = Room::findOrFail($roomId);
        return $room->users()->where('user_id', $user->id)->exists();
    } catch (\Exception $e) {
        \Log::error('Broadcasting channel error:', [
            'user_id' => $user->id,
            'room_id' => $roomId,
            'error' => $e->getMessage()
        ]);
        return false;
    }
});
