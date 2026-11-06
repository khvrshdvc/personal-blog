<?php

namespace App\Models;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'category', 'tag', 'title', 'slug', 'status', 'published_at',
        'cover', 'excerpt', 'body', 'pending',
    ];

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'status' => PostStatus::class,
            'published_at' => 'date',
            'pending' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published);
    }

    public function scopeInCategory(Builder $query, PostCategory $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function readMinutes(): int
    {
        $words = preg_match_all('/\S+/u', (string) $this->body);

        return max(1, (int) round($words / 200));
    }

    public function hasPending(): bool
    {
        return $this->pending !== null;
    }
}
