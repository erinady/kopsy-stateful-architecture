<?php

namespace App\Models;

use App\Models\AmdkProduct;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GallonLoan extends Model
{
    //
    protected $fillable = [
        'amdk_product_id',
        'member_code',
        'return_date',
        'loan_status',

        'updated_by',
    ];

    public function amdkProduct()
    {
        return $this->belongsTo(AmdkProduct::class, 'amdk_product_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_code');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
