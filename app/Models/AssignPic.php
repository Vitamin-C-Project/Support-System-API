<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignPic extends Model
{
    protected $guarded = ['id'];

    protected $with  = ['project'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
