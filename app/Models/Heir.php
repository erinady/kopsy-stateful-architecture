<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Heir extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nik',
        'name',
        'relationship',
        'contact',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
