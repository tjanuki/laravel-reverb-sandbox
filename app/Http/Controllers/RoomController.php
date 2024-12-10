<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Rooms/Index', [
            'rooms' => $user->rooms()->with('users')->latest()->get(),
            'availableUsers' => User::where('users.id', '!=', $user->id)->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Rooms/Create', [
            'availableUsers' => User::where('id', '!=', Auth::id())->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => [
                'required',
                'exists:users,id',
                Rule::notIn([Auth::id()])
            ]
        ]);

        $room = Room::create(['name' => $validated['name']]);

        // Add the current user and selected users to the room
        $userIds = array_unique([Auth::id(), ...$validated['user_ids']]);
        $room->users()->attach($userIds);

        return redirect()->route('rooms.show', $room)
            ->with('message', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        // Ensure the user has access to this room
        abort_unless($room->users->contains(Auth::id()), 403);

        // Load relationships and paginate messages
        $room->load('users');
        $messages = $room->messages()
            ->with('user')
            ->latest()
            ->paginate(50);

        return Inertia::render('ChatRoom', [
            'room' => $room,
            'messages' => $messages,
            'availableUsers' => User::whereNotIn('id', $room->users->pluck('id'))->get(),
            'canManageRoom' => $room->users()->where('users.id', Auth::id())->exists()
        ]);
    }

    public function update(Request $request, Room $room)
    {
        // Ensure user has access to the room
        abort_unless($room->users->contains(Auth::id()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $room->update($validated);

        return back()->with('message', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        // Ensure user has access to the room
        abort_unless($room->users->contains(Auth::id()), 403);

        $room->delete();

        return redirect()->route('rooms.index')
            ->with('message', 'Room deleted successfully.');
    }

    public function join(Request $request, Room $room)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => [
                'required',
                'exists:users,id',
                Rule::notIn($room->users->pluck('users.id')->toArray())
            ]
        ]);

        $room->users()->attach($validated['user_ids']);

        return back()->with('message', 'Users added to room successfully.');
    }

    public function leave(Room $room)
    {
        // Prevent the last user from leaving the room
        if ($room->users->count() <= 1) {
            return back()->with('error', 'Cannot leave room as you are the last member.');
        }

        $room->users()->detach(Auth::id());

        return redirect()->route('rooms.index')
            ->with('message', 'Left room successfully.');
    }

    public function messages(Room $room, Request $request)
    {
        abort_unless($room->users->contains(Auth::id()), 403);

        return $room->messages()
            ->with('user')
            ->latest()
            ->paginate(50);
    }
}
