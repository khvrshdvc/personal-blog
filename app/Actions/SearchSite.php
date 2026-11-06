<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Support\Uz;

class SearchSite
{
    private const PAGES = [
        ['Asosiy', '/', 'grid'],
        ['Fikrlar', '/fikrlar', 'bookOpen'],
        ['Dasturlash', '/dasturlash', 'code'],
        ['Loyihalar', '/loyihalar', 'folder'],
        ['GitHub faollik', '/faollik', 'branch'],
        ['Men haqimda', '/men', 'person'],
    ];

    public function __invoke(string $query): array
    {
        $query = trim($query);
        $pages = $this->pages($query);

        if ($query === '') {
            return [...$this->latest(), ...$pages];
        }

        $posts = $this->posts($query);

        return [
            ...array_values(array_filter($posts, fn ($p) => $p['g'] === PostCategory::Fikr->label())),
            ...array_values(array_filter($posts, fn ($p) => $p['g'] === PostCategory::Dev->label())),
            ...$this->projects($query),
            ...$pages,
        ];
    }

    private function latest(): array
    {
        return Post::published()->latestFirst()->limit(5)->get()
            ->map(fn (Post $post) => $this->postItem($post, 'Soʻnggi yozuvlar'))
            ->all();
    }

    private function posts(string $query): array
    {
        $like = '%'.addcslashes($query, '%_\\').'%';

        return Post::published()
            ->where(fn ($q) => $q
                ->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('tag', 'like', $like)
                ->orWhere('body', 'like', $like))
            ->latestFirst()
            ->limit(14)
            ->get()
            ->map(fn (Post $post) => $this->postItem($post, $post->category->label()))
            ->all();
    }

    private function projects(string $query): array
    {
        $like = '%'.addcslashes($query, '%_\\').'%';

        return Project::query()
            ->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('repo', 'like', $like)
                ->orWhere('stack', 'like', $like))
            ->limit(14)
            ->get()
            ->map(fn (Project $project) => [
                't' => $project->name.($project->description ? ' — '.$project->description : ''),
                'h' => url('/loyihalar'),
                'i' => 'folder',
                'g' => 'Loyihalar',
                's' => '',
            ])
            ->all();
    }

    private function pages(string $query): array
    {
        $needle = mb_strtolower($query);

        return collect(self::PAGES)
            ->filter(fn ($page) => $needle === '' || str_contains(mb_strtolower($page[0]), $needle))
            ->map(fn ($page) => ['t' => $page[0], 'h' => url($page[1]), 'i' => $page[2], 'g' => 'Sahifalar', 's' => ''])
            ->values()
            ->all();
    }

    private function postItem(Post $post, string $group): array
    {
        return [
            't' => $post->title,
            'h' => url('/post/'.$post->slug),
            'i' => $post->category === PostCategory::Dev ? 'code' : 'bookOpen',
            'g' => $group,
            's' => Uz::short($post->published_at),
        ];
    }
}
