<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $blog_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Blog $blog
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BlogComment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereBlogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogComment whereUserId($value)
 * @mixin \Eloquent
 */
class BlogComment extends Model
{
    protected $fillable = [
        'user_id',
        'blog_id',
        'comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }
}
