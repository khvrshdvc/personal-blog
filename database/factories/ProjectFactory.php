<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'repo' => fake()->slug(2),
            'description' => fake()->sentence(),
            'stack' => ['Laravel', 'MySQL'],
            'demo_url' => null,
        ];
    }
}
