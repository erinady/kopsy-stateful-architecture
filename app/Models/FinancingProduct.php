<?php

namespace App\Models;

use App\Models\FinancingItem;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancingProduct extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'product_code',
        'name',
        'brand',
        'specification',
        'type_id',
    ];

    public function productType()
    {
        return $this->belongsTo(ProductType::class, 'type_id');
    }

    public function financingItems()
    {
        return $this->hasMany(FinancingItem::class);
    }
}
