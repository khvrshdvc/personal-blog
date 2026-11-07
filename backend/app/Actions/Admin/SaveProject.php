<?php

namespace App\Actions\Admin;

use App\Models\Project;
use App\Support\RepoName;

class SaveProject
{
    public function __invoke(?Project $project, array $input): Project
    {
        $attributes = [];

        if (array_key_exists('name', $input)) {
            $attributes['name'] = trim($input['name']);
        }

        if (array_key_exists('repo', $input)) {
            $attributes['repo'] = RepoName::normalize($input['repo']) ?: null;
        }

        if (array_key_exists('description', $input)) {
            $attributes['description'] = trim((string) $input['description']) ?: null;
        }

        if (array_key_exists('stack', $input)) {
            $attributes['stack'] = array_slice(array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                (array) $input['stack'],
            ))), 0, 8);
        }

        if (array_key_exists('demo_url', $input)) {
            $demo = trim((string) $input['demo_url']);

            if ($demo !== '' && ! preg_match('#^https?://#i', $demo)) {
                $demo = 'https://'.$demo;
            }

            $attributes['demo_url'] = $demo !== '' && filter_var($demo, FILTER_VALIDATE_URL) ? $demo : null;
        }

        if ($project === null) {
            return Project::create($attributes + ['stack' => []]);
        }

        $project->update($attributes);

        return $project;
    }
}
