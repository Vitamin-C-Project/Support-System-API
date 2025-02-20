<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $with = ['project', 'ticket_status', 'severity', 'ticketAssign'];

    public function attachment()
    {
        // return $this->morphMany(Attachment::class, 'attachable');
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function ticket_status()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function severity()
    {
        return $this->belongsTo(Severity::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logTicket()
    {
        return $this->hasMany(LogTicket::class, 'ticket_id');
    }

    public function ticketAssign()
    {
        return $this->hasMany(TicketAssign::class, 'ticket_id');
    }
}
