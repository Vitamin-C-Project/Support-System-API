<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssign extends Model
{
    protected $guarded = [''];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
