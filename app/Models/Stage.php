<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = ['name', 'level', 'order'];

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}
