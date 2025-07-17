<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
    "name","description","price","task_limit",
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function oreders()
    {
        return $this->hasMany(Order::class);
    }
}
