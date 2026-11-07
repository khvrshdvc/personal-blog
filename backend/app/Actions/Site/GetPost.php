<?php

namespace App\Actions\Site;

use App\Models\Post;
use App\Support\Markdown;

class GetPost
{
    public function __invoke(string $slug): ?array
    {
        $post = Post::published()->where('slug', $slug)->first();

        if ($post === null) {
            return null;
        }

        $rendered = Markdown::render($post->body);

        return [
            'post' => $post,
            'html' => $rendered['html'],
            'toc' => $rendered['headings'],
            'prev' => $this->neighbour($post, newer: false),
            'next' => $this->neighbour($post, newer: true),
        ];
    }

    private function neighbour(Post $post, bool $newer): ?Post
    {
        if ($post->published_at === null) {
            return null;
        }

        $date = $post->published_at->toDateString();
        $operator = $newer ? '>' : '<';

        return Post::published()
            ->inCategory($post->category)
            ->where(fn ($q) => $q
                ->whereDate('published_at', $operator, $date)
                ->orWhere(fn ($q) => $q->whereDate('published_at', $date)->where('id', $operator, $post->id)))
            ->orderBy('published_at', $newer ? 'asc' : 'desc')
            ->orderBy('id', $newer ? 'asc' : 'desc')
            ->first();
    }
}
