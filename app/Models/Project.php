<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        "status" => 'integer',
        "type" => "array"
    ];

    protected $with = ['assignPic'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function assignPic()
    {
        return $this->hasMany(AssignPic::class, 'user_id');
    }
}
