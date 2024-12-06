<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $room = Room::create(['name' => $validated['name']]);

        // Add the current user and selected users to the room
        $userIds = array_unique([auth()->id(), ...$validated['user_ids']]);
        $room->users()->attach($userIds);

        return back()->with('message', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        // Ensure the user has access to this room
        abort_unless($room->users->contains(auth()->id()), 403);

        return Inertia::render('ChatRoom', [
            'room' => $room->load('users'),
            'messages' => $room->messages()->with('user')->latest()->take(50)->get(),
            'users' => User::whereNotIn('id', $room->users->pluck('id'))->get()
        ]);
    }
}
