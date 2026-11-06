<?php

namespace App\Actions\Admin;

use App\Models\Project;
use App\Support\RepoName;

class SaveProject
{
    public function __invoke(?Project $project, array $input): Project
    {
        $stack = array_slice(array_values(array_filter(array_map('trim', explode(',', (string) ($input['stack'] ?? ''))))), 0, 8);
        $demo = trim((string) ($input['demo_url'] ?? ''));

        if ($demo !== '' && ! preg_match('#^https?://#i', $demo)) {
            $demo = 'https://'.$demo;
        }

        $attributes = [
            'name' => trim($input['name']),
            'repo' => RepoName::normalize($input['repo'] ?? '') ?: null,
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'stack' => $stack,
            'demo_url' => $demo !== '' && filter_var($demo, FILTER_VALIDATE_URL) ? $demo : null,
        ];

        if ($project === null) {
            return Project::create($attributes);
        }

        $project->update($attributes);

        return $project;
    }
}
