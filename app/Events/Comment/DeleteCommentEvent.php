<?php

namespace App\Events\Comment;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteCommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $id) {}

    public function broadcastWith(): array
    {
        return ['id' => $this->id];
    }
    public function broadcastOn()
    {
        return new Channel('Delete.Comment.Event');
    }

    public function broadcastAs()
    {
        return 'DeleteCommentEvent';
    }
}
