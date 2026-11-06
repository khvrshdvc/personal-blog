<?php

namespace App\Actions\Admin;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Services\SettingsService;
use App\Support\ActivityStats;
use App\Support\Markdown;
use App\Support\Uz;
use Carbon\Carbon;

class GetDashboardData
{
    private const WEEK = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];

    private const MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'];

    public function __construct(private SettingsService $settings)
    {
    }

    public function __invoke(): array
    {
        $now = now();
        $posts = Post::orderByDesc('updated_at')->get();
        $published = $posts->filter->isPublished();
        $drafts = $posts->reject->isPublished();
        $pending = $published->filter->hasPending();
        $thoughts = $published->where('category', PostCategory::Fikr)->count();
        $hero = $posts->first();
        $work = $drafts->concat($pending)->sortByDesc('updated_at')->reject(fn ($p) => $hero && $p->is($hero))->take(4);
        $projects = Project::orderBy('id')->get();

        return [
            'eyebrow' => self::WEEK[$now->dayOfWeek].', '.$now->day.'-'.self::MONTHS[$now->month - 1].' · Toshkent '.$now->format('H:i'),
            'greeting' => $this->greeting($now->hour),
            'hero' => $hero ? $this->postRow($hero) + ['words' => $this->words($hero->view()['body']), 'category' => $hero->category] : null,
            'publishedCount' => $published->count(),
            'thoughtCount' => $thoughts,
            'devCount' => $published->count() - $thoughts,
            'draftCount' => $drafts->count(),
            'pendingCount' => $pending->count(),
            'words' => number_format($published->sum(fn (Post $p) => $this->words($p->body)), 0, '', ' '),
            'work' => $work->map(fn (Post $p) => $this->postRow($p))->values()->all(),
            'activity' => $this->activity($posts, $published, $now),
            'tags' => $published->pluck('tag')->filter()->countBy()->sortDesc()->all(),
            'checks' => $this->checks(),
            'projectCount' => $projects->count(),
            'projects' => $projects->take(4)->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'initials' => Uz::initials($p->name),
                'repo' => $p->repo ?: 'repo yoʻq',
            ])->all(),
        ];
    }

    private function postRow(Post $post): array
    {
        $view = $post->view();

        return [
            'id' => $post->id,
            'title' => $view['title'] ?: 'Sarlavhasiz',
            'excerpt' => $view['excerpt'] ?: (Markdown::excerpt($view['body']) ?: 'Matn yoʻq'),
            'tag' => $view['tag'],
            'status' => $post->statusKey(),
            'statusLabel' => $post->statusLabel(),
            'ago' => Uz::ago($post->updated_at),
        ];
    }

    private function words(string $text): int
    {
        return (int) preg_match_all('/\S+/u', $text);
    }

    private function greeting(int $hour): string
    {
        return match (true) {
            $hour >= 5 && $hour < 11 => 'Xayrli tong',
            $hour >= 11 && $hour < 17 => 'Xayrli kun',
            $hour >= 17 && $hour < 22 => 'Xayrli kech',
            default => 'Xayrli tun',
        };
    }

    private function activity($posts, $published, Carbon $now): array
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

        $days = ActivityStats::gridDays(fn (Carbon $date) => $counts[$date->toDateString()] ?? 0, 182);
        $last = $published->pluck('published_at')->filter()->max();
        $since = $last ? max(0, (int) $last->startOfDay()->diffInDays($now->copy()->startOfDay())) : null;

        return [
            'cells' => array_map(fn ($d) => $d === null ? null : [
                'level' => match (true) {
                    $d['c'] <= 0 => 0,
                    $d['c'] === 1 => 2,
                    $d['c'] === 2 => 3,
                    default => 4,
                },
                'title' => Uz::long($d['d']).' · '.$d['c'],
            ], $days),
            'active' => count(array_filter($days, fn ($d) => $d && $d['c'] > 0)),
            'month' => $published->filter(fn (Post $p) => $p->published_at?->format('Y-m') === $now->format('Y-m'))->count(),
            'since' => $since,
        ];
    }

    private function checks(): array
    {
        $github = trim((string) $this->settings->get('github', ''));
        $telegram = trim((string) ($this->settings->get('telegram', [])['u'] ?? ''));
        $bio = trim((string) $this->settings->get('bio', ''));
        $learn = collect($this->settings->get('learn', []))->filter(fn ($l) => trim($l['n'] ?? '') !== '')->count();
        $open = (bool) $this->settings->get('open', true);

        return [
            [$github !== '', 'GitHub', $github !== '' ? '@'.$github : 'Username kiritilmagan'],
            [$telegram !== '', 'Telegram kanal', $telegram !== '' ? 't.me/'.$telegram : 'Username kiritilmagan'],
            [$bio !== '', 'Men haqimda', $bio !== '' ? $this->words($bio).' soʻz' : 'Matn yoʻq'],
            [$learn > 0, 'Oʻrganyapman', $learn.' ta mavzu'],
            [$open, 'Ishga ochiq', $open ? 'Saytda koʻrinmoqda' : 'Oʻchirilgan'],
        ];
    }
}
