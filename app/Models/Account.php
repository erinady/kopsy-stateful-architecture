<?php

namespace App\Models;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $primaryKey = 'account_code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'account_code',
        'account_name',
        'account_category',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_code', 'account_code');
    }
}
