<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|About whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class About extends Model
{
    protected $fillable = [
        'content'
    ];
}
