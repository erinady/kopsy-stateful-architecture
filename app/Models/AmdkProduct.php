<?php

namespace App\Models;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmdkProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'amdk_product_code',
        'name',
        'stock',
        'unit_measure',
        'purchase_price',
        'stokist_price',
        'member_price',
        'brand',
        'supplier_id',
        'updated_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
