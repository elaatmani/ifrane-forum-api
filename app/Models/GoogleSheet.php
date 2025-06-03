<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoogleSheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sheet_id',
        'sheet_name',
        'is_active',
        'has_errors',
        'created_by',
        'last_synced_at',
        'orders_with_errors_count',
        'marketer_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_errors' => 'boolean',
        'orders_with_errors_count' => 'integer',
    ];

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function marketer()
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }
}
