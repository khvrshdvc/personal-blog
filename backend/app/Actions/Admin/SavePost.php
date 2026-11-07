<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Services\SlugService;

class SavePost
{
    public function __construct(
        private NormalizePostFields $normalize,
        private SlugService $slugs,
    ) {}

    public function __invoke(Post $post, array $input): Post
    {
        $data = ($this->normalize)([...$post->view(), ...$input]);

        if ($post->isPublished()) {
            $post->pending = $data === $post->fields() ? null : $data;
        } else {
            $slug = $data['slug'] !== '' ? $data['slug'] : ($this->slugs->fromTitle($data['title']) ?: 'qoralama');

            $post->fill([
                'category' => $data['category'],
                'title' => $data['title'],
                'slug' => $this->slugs->unique($slug, $post->id),
                'tag' => $data['tag'],
                'excerpt' => $data['excerpt'] === '' ? null : $data['excerpt'],
                'body' => $data['body'],
                'published_at' => $data['published_at'],
                'cover' => $data['cover'] === '' ? null : $data['cover'],
            ]);
        }

        $post->save();
        $post->touch();

        return $post;
    }
}
