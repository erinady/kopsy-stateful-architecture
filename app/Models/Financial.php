<?php

namespace App\Models;

use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    protected $fillable = [
        'member_code',
        'financial_type',
        'amount',
        'category',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
