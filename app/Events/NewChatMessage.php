<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
        
        Log::info('NewChatMessage Event Created', [
            'message_id' => $message->id,
            'thread_id' => $message->chat_thread_id,
            'sender_type' => $message->sender_type,
        ]);
    }

    public function broadcastOn(): array
    {
        $channel = new PrivateChannel('chat-thread.' . $this->message->chat_thread_id);
        
        Log::info('Broadcasting on channel: ' . $channel->name);
        
        return [$channel];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_thread_id' => $this->message->chat_thread_id,
                'body' => $this->message->body,
                'sender_type' => $this->message->sender_type,
                'created_at' => $this->message->created_at->toDateTimeString(),
            ]
        ];
    }
}