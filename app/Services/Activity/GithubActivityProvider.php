<?php

namespace App\Services\Activity;

use App\Support\ActivityStats;
use App\Support\RepoName;
use Carbon\Carbon;

class GithubActivityProvider
{
    public function __construct(private GithubClient $client)
    {
    }

    public function fetch(string $username, array $projectRepos): array
    {
        $user = $this->client->user($username);
        $login = $user['login'];

        $info = [];

        foreach ($this->client->repositories($login) as $repo) {
            $info[strtolower($repo['name'])] = [
                'lang' => $repo['language'] ?? null,
                'stars' => (int) ($repo['stargazers_count'] ?? 0),
                'pushed' => $repo['pushed_at'] ?? null,
                'url' => $repo['html_url'] ?? null,
            ];
        }

        $full = array_values(array_unique(array_filter(array_map(
            fn (?string $repo) => $this->fullName((string) $repo, $login),
            $projectRepos,
        ))));

        $runs = $full === [] ? [] : $this->client->runs(array_slice($full, 0, 8));

        $counts = $this->client->contributionYear($login);
        $range = 'soʻnggi yil';

        if ($counts === null) {
            $counts = $this->client->recentEvents($login);
            $range = 'soʻnggi 90 kun';
        }

        return [
            'real' => true,
            'login' => $login,
            'days' => ActivityStats::gridDays(fn (Carbon $date) => $counts[$date->toDateString()] ?? 0),
            'runs' => $runs,
            'info' => $info,
            'range' => $range,
        ];
    }

    private function fullName(string $repo, string $login): ?string
    {
        $repo = RepoName::normalize($repo);

        if ($repo === '') {
            return null;
        }

        return str_contains($repo, '/') ? $repo : $login.'/'.$repo;
    }
}
