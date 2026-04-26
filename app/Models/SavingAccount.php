<?php

namespace App\Models;

use App\Models\Member;
use App\Models\SavingTransaction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SavingAccount extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'saving_account_code',
        'saving_product_id',
        'saving_tenor',
        'target_amount',
        'member_code',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_code', 'user_code');
    }

    public function transactions()
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function savingProduct()
    {
        return $this->belongsTo(SavingProduct::class);
    }
}
