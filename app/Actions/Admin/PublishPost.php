<?php

namespace App\Actions\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\SlugService;
use App\Support\Markdown;
use Illuminate\Validation\ValidationException;

class PublishPost
{
    public function __construct(private SlugService $slugs)
    {
    }

    public function __invoke(Post $post, bool $strictSlug): Post
    {
        $view = $post->view();

        if ($view['title'] === '') {
            throw ValidationException::withMessages(['title' => 'Sarlavha yozilmagan']);
        }

        if (trim($view['body']) === '') {
            throw ValidationException::withMessages(['body' => 'Matn boʻsh']);
        }

        $slug = $this->slugs->clean($view['slug']) ?: ($this->slugs->fromTitle($view['title']) ?: 'yozuv-'.$post->id);

        if ($this->slugs->isTaken($slug, $post->id)) {
            if ($strictSlug && $view['slug'] !== '') {
                throw ValidationException::withMessages(['slug' => 'Bu havola band — boshqasini tanlang']);
            }

            $slug = $this->slugs->unique($slug, $post->id);
        }

        $post->fill([
            'category' => $view['category'],
            'title' => $view['title'],
            'slug' => $slug,
            'tag' => $view['tag'],
            'excerpt' => $view['excerpt'] !== '' ? $view['excerpt'] : Markdown::excerpt($view['body']),
            'body' => $view['body'],
            'published_at' => $view['published_at'] ?: now()->toDateString(),
            'cover' => $view['cover'] === '' ? null : $view['cover'],
            'status' => PostStatus::Published,
            'pending' => null,
        ]);
        $post->save();
        $post->touch();

        return $post;
    }
}
