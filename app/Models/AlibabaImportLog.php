<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlibabaImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'import_type',
        'total_products',
        'successful_imports',
        'failed_imports',
        'error_log',
        'status',
        'settings'
    ];

    protected $casts = [
        'error_log' => 'array',
        'settings' => 'array'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
