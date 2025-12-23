<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    protected $fillable = [
        'name',
    ];

    public function role()
    {
        return $this->hasOne(User::class);
    }
}
