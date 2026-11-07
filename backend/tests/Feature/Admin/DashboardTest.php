<?php

use App\Models\Post;
use App\Models\Project;
use Database\Seeders\SettingSeeder;

beforeEach(fn () => $this->actingAs(admin()));

it('returns the documented dashboard shape', function () {
    $this->seed(SettingSeeder::class);
    Post::factory()->count(2)->create();

    $this->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonStructure([
            'counts' => ['published', 'thoughts', 'dev', 'drafts', 'pending', 'words', 'projects'],
            'last_edited' => ['id', 'title', 'excerpt', 'tag', 'status', 'updated_at'],
            'resume',
            'activity' => ['days', 'active_days', 'this_month', 'days_since_last'],
            'tags',
            'checks' => [['key', 'ok', 'value']],
            'projects',
        ])
        ->assertJsonCount(182, 'activity.days')
        ->assertJsonCount(5, 'checks');
});

it('works on an empty database', function () {
    $this->getJson('/api/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('last_edited', null)
        ->assertJsonPath('resume', [])
        ->assertJsonPath('counts.published', 0)
        ->assertJsonPath('activity.days_since_last', null);
});

it('counts posts by state and category', function () {
    Post::factory()->count(2)->create();
    Post::factory()->dev()->create();
    Post::factory()->draft()->create();
    Post::factory()->create(['pending' => ['title' => 'Yangi']]);
    Project::factory()->count(2)->create();

    $this->getJson('/api/admin/dashboard')
        ->assertJsonPath('counts.published', 4)
        ->assertJsonPath('counts.thoughts', 3)
        ->assertJsonPath('counts.dev', 1)
        ->assertJsonPath('counts.drafts', 1)
        ->assertJsonPath('counts.pending', 1)
        ->assertJsonPath('counts.projects', 2);
});

it('lists drafts and pending posts to resume, without the last edited one', function () {
    $draft = Post::factory()->draft()->create(['updated_at' => now()->subDays(2)]);
    $pending = Post::factory()->create(['pending' => ['title' => 'Y'], 'updated_at' => now()->subDay()]);
    $last = Post::factory()->draft()->create(['updated_at' => now()]);

    $response = $this->getJson('/api/admin/dashboard')->assertOk();

    expect($response->json('last_edited.id'))->toBe($last->id)
        ->and(array_column($response->json('resume'), 'id'))->toBe([$pending->id, $draft->id])
        ->and($response->json('resume.0.status'))->toBe('pending');
});

it('reports setup checks from settings', function () {
    $this->seed(SettingSeeder::class);

    $checks = collect($this->getJson('/api/admin/dashboard')->json('checks'))->keyBy('key');

    expect($checks->keys()->all())->toBe(['github', 'telegram', 'bio', 'learn', 'open'])
        ->and($checks['bio']['ok'])->toBeTrue()
        ->and($checks['open']['ok'])->toBeTrue();
});

it('groups published tags by count', function () {
    Post::factory()->count(2)->create(['tag' => 'Hayot']);
    Post::factory()->create(['tag' => 'Til']);
    Post::factory()->draft()->create(['tag' => 'Qoralama']);

    $this->getJson('/api/admin/dashboard')->assertJsonPath('tags', [
        ['tag' => 'Hayot', 'count' => 2],
        ['tag' => 'Til', 'count' => 1],
    ]);
});
