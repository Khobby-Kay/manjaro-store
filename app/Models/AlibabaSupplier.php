<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlibabaSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alibaba_id',
        'contact_person',
        'phone',
        'email',
        'address',
        'rating',
        'total_products',
        'status',
        'api_credentials',
        'settings'
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'settings' => 'array',
        'rating' => 'decimal:2'
    ];

    public function products()
    {
        return $this->hasMany(AlibabaProduct::class);
    }

    public function orders()
    {
        return $this->hasMany(AlibabaOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
