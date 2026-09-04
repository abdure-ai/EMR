<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'slug', 'excerpt', 'body', 'image_path',
    'is_published', 'published_at', 'created_by',
])]
class NewsPost extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NewsPost $post) {
            if (empty($post->slug)) {
                $post->slug = static::uniqueSlug($post->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $suffix = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
