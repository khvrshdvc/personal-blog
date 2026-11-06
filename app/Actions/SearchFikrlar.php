<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;

class SearchFikrlar
{
    public function __invoke(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $like = '%'.addcslashes($query, '%_\\').'%';

        return Post::published()
            ->inCategory(PostCategory::Fikr)
            ->where(fn ($q) => $q
                ->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('body', 'like', $like))
            ->pluck('slug')
            ->all();
    }
}
