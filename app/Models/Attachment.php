<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $guarded = [''];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute()
    {
        return url('storage/' . $this->path);
    }
}
