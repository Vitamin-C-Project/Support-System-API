<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $with = ['project', 'ticket_status', 'severity'];

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
}
