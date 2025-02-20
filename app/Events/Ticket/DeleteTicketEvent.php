<?php

namespace App\Events\Ticket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteTicketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $data) {}

    // public function broadcastWith(): array
    // {
    //     return ['id' => $this->id];
    // }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    /******  efb00511-430e-44c1-ad45-400a4dbac6fc  *******/
    public function broadcastOn(): array
    {
        return [
            new Channel('Delete.Ticket.Event'),
        ];
    }

    public function broadcastAs()
    {
        return 'DeleteTicketEvent';
    }
}
