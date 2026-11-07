<?php

namespace App\Services\Activity;

use App\Support\ActivityDays;
use App\Support\RepoName;

class GithubActivityProvider
{
    public function __construct(private GithubClient $client) {}

    public function fetch(string $username, array $projectRepos): array
    {
        $user = $this->client->user($username);
        $login = $user['login'];
        $info = [];

        foreach ($this->client->repositories($login) as $repo) {
            $info[strtolower($repo['name'])] = [
                'lang' => $repo['language'] ?? null,
                'stars' => (int) ($repo['stargazers_count'] ?? 0),
                'pushed_at' => $repo['pushed_at'] ?? null,
                'url' => $repo['html_url'] ?? null,
            ];
        }

        $full = array_values(array_unique(array_filter(array_map(
            fn (?string $repo) => $this->fullName((string) $repo, $login),
            $projectRepos,
        ))));

        $runs = $full === [] ? [] : $this->client->runs(array_slice($full, 0, 8));
        $counts = $this->client->contributions($login, ActivityDays::DAYS)
            ?? $this->client->profileCalendar($login)
            ?? $this->client->recentEvents($login);

        return [
            'real' => true,
            'error' => false,
            'login' => $login,
            'days' => ActivityDays::counts(fn (string $date) => $counts[$date] ?? 0),
            'runs' => $runs,
            'info' => $info,
        ];
    }

    private function fullName(string $repo, string $login): ?string
    {
        $repo = RepoName::normalize($repo);

        if ($repo === '' || ! preg_match('#^[A-Za-z0-9._-]+(/[A-Za-z0-9._-]+)?$#', $repo)) {
            return null;
        }

        return str_contains($repo, '/') ? $repo : $login.'/'.$repo;
    }
}
