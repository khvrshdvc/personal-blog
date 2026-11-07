<?php

namespace App\Actions\Admin;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\SlugService;

class CreatePost
{
    public function __construct(
        private NormalizePostFields $normalize,
        private SlugService $slugs,
    ) {}

    public function __invoke(array $input): Post
    {
        $data = ($this->normalize)($input);
        $slug = $this->slugs->fromTitle($data['title']) ?: 'qoralama';

        return Post::create([
            'category' => $data['category'],
            'title' => $data['title'],
            'slug' => $this->slugs->unique($slug),
            'tag' => $data['tag'],
            'excerpt' => null,
            'body' => $data['body'],
            'published_at' => $data['published_at'],
            'cover' => null,
            'status' => PostStatus::Draft,
        ]);
    }
}
