<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogTicket extends Model
{
    protected $guarded  = ['id'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketStatus()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
