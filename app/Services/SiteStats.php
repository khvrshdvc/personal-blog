<?php

namespace App\Services;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class SiteStats
{
    public const KEY = 'site.count.';

    public function postCount(PostCategory $category): int
    {
        return Cache::remember(self::KEY.$category->value, 600, fn () => Post::published()->inCategory($category)->count());
    }
}
