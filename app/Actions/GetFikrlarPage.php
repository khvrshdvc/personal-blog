<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;

class GetFikrlarPage
{
    public function __invoke(): array
    {
        $posts = Post::published()->inCategory(PostCategory::Fikr)->latestFirst()->get();
        $present = $posts->pluck('tag')->filter()->unique();

        return [
            'posts' => $posts,
            'tags' => array_values(array_filter(
                config('site.categories.fikr.tags'),
                fn (string $tag) => $present->contains($tag),
            )),
        ];
    }
}
