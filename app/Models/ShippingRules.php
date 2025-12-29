<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property float|null $min_cost
 * @property float $cost
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereMinCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingRules whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShippingRules extends Model
{
    protected $fillable = [
        'name',
        'type',
        'min_cost',
        'cost',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

}
