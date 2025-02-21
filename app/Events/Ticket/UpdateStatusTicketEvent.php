<?php

namespace App\Events\Ticket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateStatusTicketEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $ticket, public $projectId) {}

    // public function broadcastWith(): array
    // {
    //     return [
    //         'id' => $this->id,
    //     ];
    // }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('Update.Status.Ticket.Event.' . $this->projectId),
            new PrivateChannel('All.Ticket.Event')
        ];
    }
    public function broadcastAs(): string
    {
        return 'UpdateStatusTicketEvent';
    }
}
