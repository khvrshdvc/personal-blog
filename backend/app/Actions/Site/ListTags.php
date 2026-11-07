<?php

namespace App\Actions\Site;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Support\Collection;

class ListTags
{
    public function __invoke(?string $category = null): Collection
    {
        $query = Post::published()->whereNotNull('tag')->where('tag', '!=', '');

        if ($category !== null) {
            $query->inCategory(PostCategory::from($category));
        }

        return $query
            ->selectRaw('tag, count(*) as count')
            ->groupBy('tag')
            ->orderByDesc('count')
            ->orderBy('tag')
            ->get()
            ->map(fn ($row) => ['tag' => $row->tag, 'count' => (int) $row->count]);
    }
}
