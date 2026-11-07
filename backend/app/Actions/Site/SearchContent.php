<?php

namespace App\Actions\Site;

use App\Models\Post;
use App\Models\Project;

class SearchContent
{
    private const LIMIT = 8;

    public function __invoke(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return Post::published()->latestFirst()->limit(5)->get()
                ->map(fn (Post $post) => $this->postItem($post))
                ->all();
        }

        $like = '%'.addcslashes($query, '%_\\').'%';

        $posts = Post::published()
            ->where(fn ($q) => $q
                ->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('tag', 'like', $like)
                ->orWhere('body', 'like', $like))
            ->latestFirst()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Post $post) => $this->postItem($post));

        $projects = Project::query()
            ->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('repo', 'like', $like)
                ->orWhere('stack', 'like', $like))
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Project $project) => [
                'type' => 'project',
                'title' => $project->name,
                'subtitle' => (string) $project->description,
                'path' => '/loyihalar',
            ]);

        return $posts->concat($projects)->take(self::LIMIT)->values()->all();
    }

    private function postItem(Post $post): array
    {
        return [
            'type' => 'post',
            'title' => $post->title,
            'subtitle' => (string) $post->tag,
            'path' => '/post/'.$post->slug,
        ];
    }
}
