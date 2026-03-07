<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularSearchQuery extends Model
{
    protected $fillable = [
        'query',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
