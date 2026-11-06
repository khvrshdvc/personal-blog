<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Services\SlugService;

class PostState
{
    public function __construct(private SlugService $slugs)
    {
    }

    public function __invoke(Post $post, ?string $requestedSlug = null): array
    {
        $view = $post->view();
        $desired = $this->slugs->clean($requestedSlug ?? $view['slug']);

        return [
            'id' => $post->id,
            'status' => $post->status->value,
            'pending' => $post->hasPending(),
            'slug_taken' => $desired !== '' && $this->slugs->isTaken($desired, $post->id),
            'updated_at' => $post->updated_at?->toIso8601String(),
            'url' => $post->isPublished() ? route('post.show', $post->slug) : null,
            'fields' => $this->fields($post, $view),
        ];
    }

    private function fields(Post $post, array $view): array
    {
        return [
            'category' => $view['category'],
            'title' => $view['title'],
            'slug' => $view['slug'],
            'tag' => $view['tag'],
            'excerpt' => $view['excerpt'],
            'body' => $view['body'],
            'published_at' => $view['published_at'],
            'cover' => $view['cover'],
            'cover_url' => Post::resolveCoverUrl($view['cover']),
        ];
    }
}
