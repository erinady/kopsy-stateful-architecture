<?php

namespace App\Models;

use App\Models\SavingAccount;
use Illuminate\Database\Eloquent\Model;

class SavingProduct extends Model
{
    protected $fillable = [
        'saving_product_name',
        'amount',
        'due_date',
    ];

    public function savingAccounts()
    {
        return $this->hasMany(SavingAccount::class);
    }
}
