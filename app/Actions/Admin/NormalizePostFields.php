<?php

namespace App\Actions\Admin;

use App\Enums\PostCategory;
use App\Services\SlugService;

class NormalizePostFields
{
    public function __construct(private SlugService $slugs)
    {
    }

    public function __invoke(array $input): array
    {
        $category = PostCategory::tryFrom((string) ($input['category'] ?? '')) ?? PostCategory::Fikr;
        $tags = config('site.categories.'.$category->value.'.tags');
        $tag = in_array($input['tag'] ?? null, $tags, true) ? $input['tag'] : $tags[0];

        return [
            'category' => $category->value,
            'title' => trim((string) ($input['title'] ?? '')),
            'slug' => $this->slugs->clean($input['slug'] ?? ''),
            'tag' => $tag,
            'excerpt' => mb_substr(trim((string) ($input['excerpt'] ?? '')), 0, 200),
            'body' => (string) ($input['body'] ?? ''),
            'published_at' => ($input['published_at'] ?? '') ?: now()->toDateString(),
            'cover' => (string) ($input['cover'] ?? ''),
        ];
    }
}
