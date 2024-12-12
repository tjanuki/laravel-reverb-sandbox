<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // Ensure the user relationship is loaded
        $this->message = $message->load('user');
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->message->room_id)
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'user_id' => $this->message->user_id,
                'room_id' => $this->message->room_id,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
                'user' => $this->message->user
            ]
        ];
    }
}
