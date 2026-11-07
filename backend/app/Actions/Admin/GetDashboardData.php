<?php

namespace App\Actions\Admin;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Services\SettingsService;

class GetDashboardData
{
    private const DAYS = 182;

    public function __construct(private SettingsService $settings) {}

    public function __invoke(): array
    {
        $posts = Post::orderByDesc('updated_at')->orderByDesc('id')->get();
        $published = $posts->filter->isPublished();
        $drafts = $posts->reject->isPublished();
        $pending = $published->filter->hasPending();
        $thoughts = $published->where('category', PostCategory::Fikr)->count();
        $last = $posts->first();
        $resume = $drafts->concat($pending)
            ->sortByDesc('updated_at')
            ->reject(fn (Post $post) => $last && $post->is($last))
            ->take(4)
            ->values();
        $projects = Project::orderBy('id')->get();

        return [
            'counts' => [
                'published' => $published->count(),
                'thoughts' => $thoughts,
                'dev' => $published->count() - $thoughts,
                'drafts' => $drafts->count(),
                'pending' => $pending->count(),
                'words' => (int) $published->sum(fn (Post $post) => $post->words()),
                'projects' => $projects->count(),
            ],
            'last_edited' => $last,
            'resume' => $resume,
            'activity' => $this->activity($posts, $published),
            'tags' => $published->pluck('tag')->filter()->countBy()->sortDesc()
                ->map(fn (int $count, string $tag) => ['tag' => $tag, 'count' => $count])->values()->all(),
            'checks' => $this->checks(),
            'projects' => $projects->take(4)->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'repo' => $project->repo,
            ])->all(),
        ];
    }

    private function activity($posts, $published): array
    {
        $counts = [];

        foreach ($published as $post) {
            if ($post->published_at) {
                $key = $post->published_at->toDateString();
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        foreach ($posts as $post) {
            $key = $post->updated_at->toDateString();
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $today = now()->startOfDay();
        $days = [];

        for ($i = self::DAYS - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->toDateString();
            $days[] = ['d' => $date, 'c' => $counts[$date] ?? 0];
        }

        $lastPublished = $published->pluck('published_at')->filter()->max();

        return [
            'days' => $days,
            'active_days' => count(array_filter($days, fn (array $day) => $day['c'] > 0)),
            'this_month' => $published->filter(fn (Post $post) => $post->published_at?->format('Y-m') === now()->format('Y-m'))->count(),
            'days_since_last' => $lastPublished ? max(0, (int) $lastPublished->startOfDay()->diffInDays($today)) : null,
        ];
    }

    private function checks(): array
    {
        $github = trim((string) $this->settings->get('github', ''));
        $telegram = trim((string) ($this->settings->get('telegram', [])['u'] ?? ''));
        $bio = trim((string) $this->settings->get('bio', ''));
        $learn = collect($this->settings->get('learn', []))->filter(fn ($item) => trim($item['n'] ?? '') !== '')->count();
        $open = (bool) $this->settings->get('open', true);

        return [
            ['key' => 'github', 'ok' => $github !== '', 'value' => $github],
            ['key' => 'telegram', 'ok' => $telegram !== '', 'value' => $telegram],
            ['key' => 'bio', 'ok' => $bio !== '', 'value' => (string) preg_match_all('/\S+/u', $bio)],
            ['key' => 'learn', 'ok' => $learn > 0, 'value' => (string) $learn],
            ['key' => 'open', 'ok' => $open, 'value' => $open ? 'true' : 'false'],
        ];
    }
}
