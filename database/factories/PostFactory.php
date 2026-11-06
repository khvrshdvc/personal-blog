<?php

namespace Database\Factories;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'category' => PostCategory::Fikr,
            'tag' => fake()->word(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'status' => PostStatus::Published,
            'published_at' => fake()->date(),
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => PostStatus::Draft]);
    }

    public function dev(): static
    {
        return $this->state(['category' => PostCategory::Dev]);
    }
}
