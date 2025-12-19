<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'price',
        'profit',
        'stock',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
