<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Services\Activity\ActivityProvider;
use App\Services\SettingsService;
use App\Support\ActivityStats;
use App\Support\Uz;

class GetHomeData
{
    private const CI_LABELS = [
        'success' => 'CI oʻtdi',
        'failure' => 'CI xato',
        'running' => 'CI ishlamoqda',
        'skipped' => 'CI oʻtkazildi',
    ];

    public function __construct(
        private SettingsService $settings,
        private ActivityProvider $activity,
    ) {
    }

    public function __invoke(): array
    {
        $thoughts = Post::published()->inCategory(PostCategory::Fikr)->latestFirst()->limit(4)->get();
        $thoughtCount = Post::published()->inCategory(PostCategory::Fikr)->count();
        $activity = $this->activity->get();
        $stats = ActivityStats::summarize($activity['days']);
        $runs = $activity['runs'];
        $projects = Project::orderBy('id')->get();

        return [
            'latest' => $thoughts->first(),
            'recent' => $thoughts,
            'thoughtCount' => $thoughtCount,
            'learn' => $this->learn(),
            'telegram' => $this->telegram(),
            'projectCount' => $projects->count(),
            'projects' => $projects->take(3)->map(fn (Project $project) => [
                'initials' => Uz::initials($project->name),
                'name' => $project->name ?: 'Nomsiz',
                'status' => $this->lastStatus($project, $runs),
            ])->all(),
            'tags' => array_slice(array_merge(...array_map(fn ($g) => $g[3], config('site.stack'))), 0, 11),
            'teach' => config('site.teach'),
            'github' => [
                'total' => $stats['total'],
                'streak' => $stats['streak'],
                'cells' => array_map(
                    fn ($day) => $day === null ? null : ActivityStats::level($day['c']),
                    array_slice($activity['days'], -119),
                ),
                'runs' => array_slice($runs, 0, 4),
            ],
        ];
    }

    private function learn(): array
    {
        return collect($this->settings->get('learn', []))
            ->filter(fn ($l) => trim($l['n'] ?? '') !== '')
            ->map(fn ($l) => [
                'name' => $l['n'],
                'percent' => max(0, min(100, (int) round((float) ($l['p'] ?? 0)))),
            ])
            ->values()
            ->all();
    }

    private function telegram(): array
    {
        $tg = $this->settings->get('telegram', []);

        return [
            'username' => trim($tg['u'] ?? ''),
            'name' => $tg['n'] ?? '',
            'posts' => collect($tg['posts'] ?? [])
                ->filter(fn ($p) => trim($p['t'] ?? '') !== '')
                ->sortBy(fn ($p) => (string) ($p['d'] ?? ''))
                ->values()
                ->all(),
        ];
    }

    private function lastStatus(Project $project, array $runs): ?array
    {
        $key = strtolower(last(explode('/', (string) $project->repo)));

        foreach ($runs as $run) {
            if (strtolower($run['key']) === $key) {
                return ['status' => $run['status'], 'label' => self::CI_LABELS[$run['status']]];
            }
        }

        return null;
    }
}
