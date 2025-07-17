<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        "title","description","video","image","user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function subtask()
    {
        return $this->hasMany(Subtask::class);
    }
}
