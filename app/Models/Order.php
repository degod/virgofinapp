<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'side',
        'price',
        'amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: scopes for convenience
    public function scopeOpen($query)
    {
        return $query->where('status', 1);
    }

    public function scopeFilled($query)
    {
        return $query->where('status', 2);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 3);
    }
}
