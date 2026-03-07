<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAlert extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'target_price',
        'is_active',
        'last_notified_price',
        'notified_at',
    ];

    protected $casts = [
        'target_price' => 'float',
        'is_active' => 'boolean',
        'last_notified_price' => 'float',
        'notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
