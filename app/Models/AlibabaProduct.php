<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlibabaProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'alibaba_product_id',
        'supplier_id',
        'title',
        'description',
        'original_price',
        'wholesale_price',
        'retail_price',
        'min_order_quantity',
        'stock_quantity',
        'images',
        'specifications',
        'shipping_info',
        'status',
        'last_sync_at',
        'local_product_id'
    ];

    protected $casts = [
        'images' => 'array',
        'specifications' => 'array',
        'shipping_info' => 'array',
        'original_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'last_sync_at' => 'datetime'
    ];

    public function supplier()
    {
        return $this->belongsTo(AlibabaSupplier::class);
    }

    public function localProduct()
    {
        return $this->belongsTo(Product::class, 'local_product_id');
    }

    public function orders()
    {
        return $this->hasMany(AlibabaOrder::class);
    }

    public function scopeImported($query)
    {
        return $query->where('status', 'imported');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }
}
