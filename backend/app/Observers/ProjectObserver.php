<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\FrontendRevalidator;
use App\Services\SettingsService;
use App\Support\RepoName;

class ProjectObserver
{
    public function __construct(
        private FrontendRevalidator $revalidator,
        private SettingsService $settings,
    ) {}

    public function saved(Project $project): void
    {
        $this->forget($project->repo);
        $this->revalidator->queue('projects');
    }

    public function deleted(Project $project): void
    {
        $key = $this->key($project->repo);

        if ($key !== '') {
            $hidden = array_values(array_unique([...(array) $this->settings->get('github_hidden', []), $key]));
            $this->settings->put('github_hidden', $hidden);
        }

        $this->revalidator->queue('projects');
    }

    private function forget(?string $repo): void
    {
        $key = $this->key($repo);
        $hidden = (array) $this->settings->get('github_hidden', []);

        if ($key !== '' && in_array($key, $hidden, true)) {
            $this->settings->put('github_hidden', array_values(array_diff($hidden, [$key])));
        }
    }

    private function key(?string $repo): string
    {
        return strtolower(last(explode('/', RepoName::normalize($repo))));
    }
}
