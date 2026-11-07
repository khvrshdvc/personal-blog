<?php

namespace App\Actions\Site;

use App\Models\Project;
use App\Services\Activity\ActivityProvider;
use App\Support\RepoName;

class GetRepoInfo
{
    public function __construct(private ActivityProvider $provider) {}

    public function __invoke(Project $project): array
    {
        $empty = ['lang' => null, 'stars' => null, 'pushed_at' => null, 'run' => null];
        $key = strtolower(last(explode('/', RepoName::normalize($project->repo))));

        if ($key === '') {
            return $empty;
        }

        $data = $this->provider->get();
        $info = $data['info'][$key] ?? null;

        if ($info === null) {
            return $empty;
        }

        $run = collect($data['runs'])->first(fn (array $item) => $item['key'] === $key);

        return [
            'lang' => $info['lang'],
            'stars' => $info['stars'],
            'pushed_at' => $info['pushed_at'],
            'run' => $run ? ['status' => $run['status'], 'at' => $run['at']] : null,
        ];
    }
}
