<?php

use App\Models\Project;
use App\Models\Setting;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;

it('lists projects in the documented shape', function () {
    Project::factory()->create(['name' => 'A', 'stack' => ['PHP', 'Redis']]);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonStructure([['id', 'name', 'repo', 'description', 'stack', 'demo_url']])
        ->assertJsonPath('0.stack', ['PHP', 'Redis']);
});

it('returns an empty array when there are no projects', function () {
    $this->getJson('/api/projects')->assertOk()->assertExactJson([]);
});

it('returns seeded projects', function () {
    $this->seed(ProjectSeeder::class);

    expect($this->getJson('/api/projects')->json())->toHaveCount(5);
});

it('exposes public settings with the documented keys', function () {
    $this->seed(SettingSeeder::class);

    $this->getJson('/api/settings')
        ->assertOk()
        ->assertJsonStructure(['github', 'open', 'bio', 'now', 'learn', 'telegram'])
        ->assertJsonCount(4, 'now')
        ->assertJsonCount(3, 'learn')
        ->assertJsonCount(2, 'telegram.posts');
});

it('never exposes non-public setting keys', function () {
    $this->seed(SettingSeeder::class);
    Setting::create(['key' => 'github_token', 'value' => 'sir']);

    $this->getJson('/api/settings')->assertJsonMissingPath('github_token');
});

it('returns nulls for unset settings', function () {
    $this->getJson('/api/settings')->assertOk()->assertJson(['bio' => null, 'now' => null]);
});
