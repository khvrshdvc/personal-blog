<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Services\Activity\ActivityProvider;
use App\Services\SettingsService;
use App\Support\ActivityStats;
use App\Support\Uz;

class GetDevPage
{
    public function __construct(
        private SettingsService $settings,
        private ActivityProvider $activity,
    ) {
    }

    public function __invoke(string $tab): array
    {
        return match ($tab) {
            'loyihalar' => ['tab' => $tab] + $this->projects(),
            'faollik' => ['tab' => $tab] + $this->activityTab(),
            default => ['tab' => 'yozuvlar', 'posts' => Post::published()->inCategory(PostCategory::Dev)->latestFirst()->get()],
        };
    }

    private function projects(): array
    {
        $data = $this->activity->get();
        $learn = collect($this->settings->get('learn', []))
            ->pluck('n')
            ->map(fn ($name) => mb_strtolower(trim((string) $name)))
            ->filter()
            ->all();

        $projects = Project::orderBy('id')->get()->map(function (Project $project) use ($data) {
            $key = strtolower(last(explode('/', (string) $project->repo)));
            $info = $data['info'][$key] ?? [];
            $run = collect($data['runs'])->first(fn ($r) => strtolower($r['key']) === $key);
            $full = $this->fullRepo((string) $project->repo, $data['login']);

            return [
                'name' => $project->name ?: 'Nomsiz loyiha',
                'initials' => Uz::initials($project->name),
                'description' => $project->description,
                'stack' => $project->stack ?? [],
                'demo_url' => $project->demo_url,
                'has_repo' => (bool) $project->repo,
                'code_url' => $data['real'] ? ($info['url'] ?? 'https://github.com/'.$full) : null,
                'run' => $run,
                'lang' => $info['lang'] ?? null,
                'stars' => $info['stars'] ?? 0,
                'pushed' => $info['pushed'] ?? null,
            ];
        });

        $stack = array_map(fn (array $group) => [
            'name' => $group[0],
            'icon' => $group[1],
            'items' => array_map(fn (string $item) => [
                'name' => $item,
                'on' => collect($learn)->contains(fn ($l) => $this->mentions($l, $item)),
            ], $group[3]),
        ], config('site.stack'));

        return ['projects' => $projects, 'stack' => $stack];
    }

    private function activityTab(): array
    {
        $data = $this->activity->get();
        $days = $data['days'];
        $summary = ActivityStats::summarize($days);

        return [
            'real' => $data['real'],
            'error' => $data['error'] ?? false,
            'range' => $data['range'],
            'total' => $summary['total'],
            'activeDays' => count(array_filter($days, fn ($d) => $d && $d['c'] > 0)),
            'streak' => $summary['streak'],
            'months' => $this->months($days),
            'cells' => array_map(fn ($d) => $d === null ? null : $d + ['level' => ActivityStats::level($d['c'])], $days),
            'runs' => array_slice($data['runs'], 0, 8),
        ];
    }

    private function months(array $days): array
    {
        $months = [];
        $previous = -1;
        $month = fn (?array $day) => $day ? (int) substr($day['d'], 5, 2) - 1 : null;

        for ($w = 0; $w < 53; $w++) {
            $cell = $days[$w * 7] ?? null;

            if (! $cell) {
                continue;
            }

            $m = $month($cell);

            if ($m !== $previous) {
                $skip = $w === 0 && isset($days[14]) && $month($days[14]) !== $m;

                if (! $skip && $w < 51) {
                    $months[] = ['column' => $w + 1, 'label' => Uz::SHORT[$m]];
                }

                $previous = $m;
            }
        }

        return $months;
    }

    private function mentions(string $text, string $item): bool
    {
        return (bool) preg_match('/(?<![\p{L}\p{N}])'.preg_quote(mb_strtolower($item), '/').'(?![\p{L}\p{N}])/u', $text);
    }

    private function fullRepo(string $repo, ?string $login): string
    {
        $repo = trim(preg_replace(['#^https?://(www\.)?github\.com/#i', '#\.git$#'], '', trim($repo)), '/');

        if ($repo === '') {
            return '';
        }

        return str_contains($repo, '/') ? implode('/', array_slice(explode('/', $repo), 0, 2)) : ($login ? $login.'/'.$repo : $repo);
    }
}
