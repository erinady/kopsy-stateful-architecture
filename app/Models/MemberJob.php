<?php

namespace App\Models;

use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class MemberJob extends Model
{
    protected $fillable = [
        'member_code',
        'job_title',
        'company_or_business_name',
        'business_field',
        'tenure_year',
        'business_field',
        'workplace_address',
        'workplace_contact',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
