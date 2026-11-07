<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\FrontendRevalidator;

class PostObserver
{
    private const PUBLIC_FIELDS = ['category', 'tag', 'title', 'slug', 'status', 'published_at', 'cover', 'excerpt', 'body'];

    public function __construct(private FrontendRevalidator $revalidator) {}

    public function saved(Post $post): void
    {
        $wasPublished = ($post->getOriginal('status') ?? $post->status) === PostStatus::Published;
        $isPublished = $post->status === PostStatus::Published;

        if (! $wasPublished && ! $isPublished) {
            return;
        }

        if (! $post->wasRecentlyCreated && ! $post->wasChanged(self::PUBLIC_FIELDS)) {
            return;
        }

        $this->revalidate($post);
    }

    public function deleted(Post $post): void
    {
        if ($post->status === PostStatus::Published) {
            $this->revalidate($post);
        }
    }

    private function revalidate(Post $post): void
    {
        $slugs = array_unique(array_filter([$post->slug, $post->getOriginal('slug')]));

        $this->revalidator->queue('posts', ...array_map(fn (string $slug) => 'post:'.$slug, $slugs));
    }
}
