<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'total_price',
        'tenor',
        'amount_ins',
        'financing_id',
    ];

    public function financing()
    {
        return $this->belongsTo(Financing::class);
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }
}
