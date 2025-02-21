<?php

namespace App\Events\Ticket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateTicketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $data, public $projectId = null) {}

    public function broadcastOn(): mixed
    {

        return [new PrivateChannel('Create.Ticket.Event.' . $this->projectId), new PrivateChannel('All.Ticket.Event')];
    }

    public function broadcastAs()
    {
        return "CreateTicketEvent";
    }
}
