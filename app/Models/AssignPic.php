<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignPic extends Model
{
    protected $guarded = ['id'];

    public function project()
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }
}
