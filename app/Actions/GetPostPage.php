<?php

namespace App\Actions;

use App\Models\Post;
use App\Support\Markdown;

class GetPostPage
{
    public function __invoke(string $slug, bool $canSeeDrafts): ?array
    {
        $post = Post::where('slug', $slug)->first();

        if ($post === null || (! $post->isPublished() && ! $canSeeDrafts)) {
            return null;
        }

        $rendered = Markdown::render($post->body);
        $published = $post->isPublished();

        return [
            'post' => $post,
            'html' => $rendered['html'],
            'headings' => $rendered['headings'],
            'draft' => ! $published,
            'newer' => $published ? $this->neighbour($post, newer: true) : null,
            'older' => $published ? $this->neighbour($post, newer: false) : null,
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
