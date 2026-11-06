<?php

namespace App\Services;

use App\Enums\PostCategory;
use App\Models\Post;

class SiteStats
{
    public function postCount(PostCategory $category): int
    {
        return Post::published()->inCategory($category)->count();
    }
}
