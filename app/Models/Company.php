<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['type' => 'array', 'status' => 'integer'];
}
