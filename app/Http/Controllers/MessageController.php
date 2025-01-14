<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat', [
            'messages' => Message::with('user')->latest()->take(50)->get(),
            'users' => \App\Models\User::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'room_id' => 'required|exists:rooms,id'
        ]);

        $room = Room::findOrFail($validated['room_id']);
        abort_unless($room->users->contains(auth()->id()), 403);

        $message = Message::create([
            'user_id' => auth()->id(),
            'room_id' => $validated['room_id'],
            'message' => $validated['message']
        ]);

        // Load the user relationship
        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        // Return an Inertia response instead of JSON
        return back()->with('message', 'Message sent successfully.');
    }
}
