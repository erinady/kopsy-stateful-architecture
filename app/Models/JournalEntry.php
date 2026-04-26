<?php

namespace App\Models;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'fin_trans_id',
        'account_code',
        'user_id',
        'position',
        'nominal',
        'updated_by',
    ];

    // Relasi balik ke Financial Transaction
    public function financialTransaction()
    {
        return $this->belongsTo(FinancialTransaction::class, 'fin_trans_id', 'fin_trans_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
