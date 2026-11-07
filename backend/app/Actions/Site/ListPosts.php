<?php

namespace App\Actions\Site;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPosts
{
    public function __invoke(array $filters): LengthAwarePaginator
    {
        $query = Post::published()->latestFirst();

        if (! empty($filters['category'])) {
            $query->inCategory(PostCategory::from($filters['category']));
        }

        if (! empty($filters['tag'])) {
            $query->where('tag', $filters['tag']);
        }

        if (! empty($filters['q'])) {
            $like = '%'.addcslashes($filters['q'], '%_\\').'%';

            $query->where(fn ($q) => $q
                ->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('tag', 'like', $like)
                ->orWhere('body', 'like', $like));
        }

        return $query->paginate($filters['per_page'] ?? 12)->withQueryString();
    }
}
