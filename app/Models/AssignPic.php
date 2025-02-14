<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignPic extends Model
{
    protected $guarded = ['id'];

    protected $with  = ['user'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
