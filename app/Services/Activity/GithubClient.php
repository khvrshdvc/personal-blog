<?php

namespace App\Services\Activity;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GithubClient
{
    private const API = 'https://api.github.com';

    public function hasToken(): bool
    {
        return (bool) config('services.github.token');
    }

    public function user(string $username): array
    {
        return $this->request()->get(self::API.'/users/'.rawurlencode($username))->throw()->json();
    }

    public function repositories(string $login): array
    {
        return $this->safe(fn () => $this->request()->get(self::API.'/users/'.rawurlencode($login).'/repos', ['sort' => 'pushed', 'per_page' => 100])->throw()->json()) ?? [];
    }

    public function runs(array $fullNames): array
    {
        $responses = Http::pool(fn ($pool) => array_map(
            fn (string $full) => $this->configure($pool->as($full))->get(self::API.'/repos/'.$full.'/actions/runs', ['per_page' => 6]),
            $fullNames,
        ));

        $runs = [];

        foreach ($fullNames as $full) {
            $response = $responses[$full] ?? null;

            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                foreach ($response->json('workflow_runs', []) as $run) {
                    $runs[] = $this->mapRun($run, $full);
                }
            }
        }

        usort($runs, fn ($a, $b) => strcmp($b['at'], $a['at']));

        return array_slice($runs, 0, 12);
    }

    public function contributionYear(string $login): ?array
    {
        if (! $this->hasToken()) {
            return null;
        }

        $query = 'query($login:String!,$from:DateTime!,$to:DateTime!){user(login:$login){contributionsCollection(from:$from,to:$to){contributionCalendar{weeks{contributionDays{date contributionCount}}}}}}';

        $data = $this->safe(fn () => $this->request()->post(self::API.'/graphql', [
            'query' => $query,
            'variables' => [
                'login' => $login,
                'from' => now('UTC')->subDays(364)->startOfDay()->toIso8601String(),
                'to' => now('UTC')->toIso8601String(),
            ],
        ])->throw()->json('data.user.contributionsCollection.contributionCalendar.weeks'));

        if (! is_array($data)) {
            return null;
        }

        $counts = [];

        foreach ($data as $week) {
            foreach ($week['contributionDays'] ?? [] as $day) {
                $counts[$day['date']] = (int) $day['contributionCount'];
            }
        }

        return $counts;
    }

    public function recentEvents(string $login): array
    {
        $events = $this->safe(fn () => $this->request()->get(self::API.'/users/'.rawurlencode($login).'/events/public', ['per_page' => 100])->throw()->json()) ?? [];
        $counts = [];

        foreach ($events as $event) {
            $date = \Carbon\Carbon::parse($event['created_at'])->timezone(config('app.timezone'))->toDateString();
            $size = $event['type'] === 'PushEvent'
                ? ($event['payload']['size'] ?? count($event['payload']['commits'] ?? []) ?: 1)
                : 1;
            $counts[$date] = ($counts[$date] ?? 0) + (int) $size;
        }

        return $counts;
    }

    private function mapRun(array $run, string $full): array
    {
        $completed = ($run['status'] ?? '') === 'completed';
        $conclusion = $run['conclusion'] ?? null;
        $status = ! $completed ? 'running' : match ($conclusion) {
            'success' => 'success',
            'skipped', 'cancelled', 'neutral' => 'skipped',
            default => 'failure',
        };
        $started = $run['run_started_at'] ?? $run['created_at'];
        $title = $run['display_title'] ?? ($run['head_commit']['message'] ?? $run['name'] ?? '');
        $repo = last(explode('/', $full));

        return [
            'key' => strtolower($repo),
            'repo' => $repo,
            'workflow' => $run['name'] ?? 'Workflow',
            'title' => trim(strtok((string) $title, "\n")),
            'branch' => $run['head_branch'] ?? '',
            'num' => $run['run_number'] ?? null,
            'status' => $status,
            'dur' => $completed ? max(0, (int) \Carbon\Carbon::parse($started)->diffInSeconds(\Carbon\Carbon::parse($run['updated_at']), false)) : null,
            'at' => \Carbon\Carbon::parse($run['created_at'])->toIso8601String(),
            'url' => $run['html_url'] ?? null,
        ];
    }

    private function request(): PendingRequest
    {
        return $this->configure(Http::withOptions([]));
    }

    private function configure(PendingRequest $request): PendingRequest
    {
        $request = $request
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28', 'User-Agent' => 'personal-blog'])
            ->timeout((int) config('services.github.timeout'));

        $token = config('services.github.token');

        return $token ? $request->withToken($token) : $request;
    }

    private function safe(\Closure $call): mixed
    {
        try {
            return $call();
        } catch (RequestException|\Illuminate\Http\Client\ConnectionException) {
            return null;
        }
    }
}
