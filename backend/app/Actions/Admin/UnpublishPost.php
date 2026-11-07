<?php

namespace App\Actions\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\SlugService;

class UnpublishPost
{
    public function __construct(private SlugService $slugs) {}

    public function __invoke(Post $post): Post
    {
        if ($post->hasPending()) {
            $view = $post->view();
            $post->fill([
                'category' => $view['category'],
                'title' => $view['title'],
                'slug' => $this->slugs->unique($this->slugs->clean($view['slug']) ?: ($this->slugs->fromTitle($view['title']) ?: 'qoralama'), $post->id),
                'tag' => $view['tag'],
                'excerpt' => $view['excerpt'] === '' ? null : $view['excerpt'],
                'body' => $view['body'],
                'published_at' => $view['published_at'] ?: now()->toDateString(),
                'cover' => $view['cover'] === '' ? null : $view['cover'],
            ]);
        }

        $post->status = PostStatus::Draft;
        $post->pending = null;
        $post->save();
        $post->touch();

        return $post;
    }
}
