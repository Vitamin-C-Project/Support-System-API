<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = ['id'];


    const SUPER_ADMIN = 'Super Admin';
    const ADMIN = 'Admin';
    const PIC_PROJECT = 'PIC Project';
    const PIC_EXECUTOR = 'PIC Executor';
}
