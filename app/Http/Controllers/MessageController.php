<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
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
        $message = Message::create([
            'user_id' => auth()->id(),
            'message' => $request->message
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return back();
    }
}
