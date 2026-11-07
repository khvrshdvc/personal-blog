<?php

namespace App\Models;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Services\SiteStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        $flush = function () {
            foreach (PostCategory::cases() as $category) {
                Cache::forget(SiteStats::KEY.$category->value);
            }
        };

        static::saved($flush);
        static::deleted($flush);
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

    public function coverUrl(): ?string
    {
        return self::resolveCoverUrl($this->cover);
    }

    public static function resolveCoverUrl(?string $cover): ?string
    {
        if (! $cover) {
            return null;
        }

        return preg_match('#^(https?://|/(?!/))#i', $cover) ? $cover : asset('storage/'.$cover);
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published;
    }

    public function tagColor(): string
    {
        return config('site.tag_colors.'.$this->tag, '#8e8e93');
    }

    public const FIELDS = ['category', 'title', 'slug', 'tag', 'excerpt', 'body', 'published_at', 'cover'];

    public function fields(): array
    {
        return [
            'category' => $this->category->value,
            'title' => (string) $this->title,
            'slug' => (string) $this->slug,
            'tag' => (string) $this->tag,
            'excerpt' => (string) $this->excerpt,
            'body' => (string) $this->body,
            'published_at' => $this->published_at?->toDateString() ?? '',
            'cover' => (string) $this->cover,
        ];
    }

    public function view(): array
    {
        $fields = $this->fields();

        if (! $this->hasPending()) {
            return $fields;
        }

        foreach (self::FIELDS as $key) {
            $fields[$key] = (string) ($this->pending[$key] ?? '');
        }

        return $fields;
    }

    public function statusKey(): string
    {
        if (! $this->isPublished()) {
            return 'draft';
        }

        return $this->hasPending() ? 'chg' : 'pub';
    }

    public function statusLabel(): string
    {
        return match ($this->statusKey()) {
            'draft' => 'Qoralama',
            'chg' => 'Oʻzgarish bor',
            default => 'Chop etilgan',
        };
    }

    public function hasPending(): bool
    {
        return $this->pending !== null;
    }
}
