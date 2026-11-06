<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = json_decode(file_get_contents(__DIR__.'/data/projects.json'), true);

        foreach ($projects as $project) {
            Project::updateOrCreate(['repo' => $project['repo']], $project);
        }
    }
}
