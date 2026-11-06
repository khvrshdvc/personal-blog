<?php

namespace App\Services;

use App\Models\Post;
use App\Support\Markdown;

class SlugService
{
    public function clean(?string $slug): string
    {
        $slug = preg_replace('/[^a-z0-9-]/', '-', mb_strtolower((string) $slug));

        return trim($slug, '-');
    }

    public function fromTitle(string $title): string
    {
        return Markdown::slugify($title);
    }

    public function isTaken(string $slug, ?int $ignoreId = null): bool
    {
        return Post::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function unique(string $slug, ?int $ignoreId = null): string
    {
        $candidate = $slug;
        $n = 2;

        while ($this->isTaken($candidate, $ignoreId)) {
            $candidate = $slug.'-'.$n++;
        }

        return $candidate;
    }
}
