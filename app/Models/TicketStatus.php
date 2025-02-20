<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $guarded = ['id'];


    public function ticket()
    {
        return $this->hasMany(Ticket::class, 'ticket_status_id');
    }
}
