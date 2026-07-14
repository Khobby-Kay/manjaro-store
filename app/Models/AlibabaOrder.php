<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlibabaOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_order_id',
        'alibaba_order_id',
        'supplier_id',
        'product_id',
        'quantity',
        'total_cost',
        'shipping_cost',
        'status',
        'tracking_number',
        'notes',
        'order_data'
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'order_data' => 'array'
    ];

    public function localOrder()
    {
        return $this->belongsTo(Order::class, 'local_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(AlibabaSupplier::class);
    }

    public function product()
    {
        return $this->belongsTo(AlibabaProduct::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
