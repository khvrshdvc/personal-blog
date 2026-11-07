<?php

namespace App\Actions\Site;

use App\Models\Project;
use App\Services\Activity\ActivityProvider;
use App\Support\RepoName;

class GetRuns
{
    public const LIMIT = 8;

    public function __construct(private ActivityProvider $provider) {}

    public function __invoke(?Project $project = null): array
    {
        $runs = $this->provider->get()['runs'];

        if ($project !== null) {
            $key = $this->key($project);
            $runs = $key === '' ? [] : array_values(array_filter($runs, fn (array $run) => $run['key'] === $key));
        }

        return array_map(fn (array $run) => [
            'status' => $run['status'],
            'workflow' => $run['workflow'],
            'title' => $run['title'],
            'repo' => $run['repo'],
            'branch' => $run['branch'] ?: null,
            'num' => $run['num'],
            'url' => $run['url'],
            'at' => $run['at'],
            'dur' => $run['dur'],
        ], array_slice($runs, 0, self::LIMIT));
    }

    private function key(Project $project): string
    {
        return strtolower(last(explode('/', RepoName::normalize($project->repo))));
    }
}
