<?php

namespace App\Actions\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\SlugService;

class SavePost
{
    public function __construct(
        private NormalizePostFields $normalize,
        private SlugService $slugs,
    ) {
    }

    public function __invoke(?Post $post, array $input): ?Post
    {
        $data = ($this->normalize)($input);

        if ($post === null) {
            if ($data['title'] === '' && trim($data['body']) === '') {
                return null;
            }

            return Post::create($this->draftAttributes($data, null) + ['status' => PostStatus::Draft]);
        }

        if ($post->isPublished()) {
            $post->pending = $data === $post->fields() ? null : $data;
            $post->save();
        } else {
            $post->fill($this->draftAttributes($data, $post->id));
            $post->save();
        }

        $post->touch();

        return $post;
    }

    private function draftAttributes(array $data, ?int $id): array
    {
        $slug = $data['slug'] !== '' ? $data['slug'] : ($this->slugs->fromTitle($data['title']) ?: 'qoralama');

        return [
            'category' => $data['category'],
            'title' => $data['title'],
            'slug' => $this->slugs->unique($slug, $id),
            'tag' => $data['tag'],
            'excerpt' => $data['excerpt'] === '' ? null : $data['excerpt'],
            'body' => $data['body'],
            'published_at' => $data['published_at'],
            'cover' => $data['cover'] === '' ? null : $data['cover'],
        ];
    }
}
