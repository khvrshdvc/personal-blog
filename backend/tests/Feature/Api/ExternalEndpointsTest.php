<?php

use App\Models\Project;
use App\Models\Setting;
use App\Support\ResilientCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('returns the documented activity shape with demo data', function () {
    Http::fake();

    $response = $this->getJson('/api/activity')->assertOk()->assertJsonStructure(['days' => [['d', 'c']], 'total', 'real']);

    expect($response->json('days'))->toHaveCount(60)
        ->and($response->json('real'))->toBeFalse()
        ->and($response->json('total'))->toBe(array_sum(array_column($response->json('days'), 'c')));
});

it('returns real activity once a username is connected', function () {
    config(['services.github.token' => 'maxfiy-token']);
    fakeGithub();
    connectGithub();

    $this->getJson('/api/activity')->assertOk()->assertJsonPath('real', true)->assertJsonPath('total', 5);
});

it('lists the latest runs in the documented shape', function () {
    fakeGithub();
    connectGithub();

    $this->getJson('/api/runs')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonStructure([['status', 'workflow', 'title', 'repo', 'branch', 'num', 'url', 'at', 'dur']])
        ->assertJsonPath('1.title', 'fix: summa')
        ->assertJsonPath('1.url', 'https://github.com/Utkirbek/crm-system/actions/runs/1')
        ->assertJsonMissingPath('0.key');
});

it('caps runs at eight even for demo data', function () {
    Http::fake();

    $runs = $this->getJson('/api/runs')->assertOk()->json();

    expect(count($runs))->toBeLessThanOrEqual(8)->and($runs)->not->toBeEmpty();
});

it('lists runs of a single project', function () {
    fakeGithub();
    $project = connectGithub();
    $other = Project::factory()->create(['repo' => null]);

    $this->getJson("/api/projects/{$project->id}/runs")->assertOk()->assertJsonCount(2)->assertJsonPath('0.repo', 'crm-system');
    $this->getJson("/api/projects/{$other->id}/runs")->assertOk()->assertExactJson([]);
});

it('returns repo info with the latest run', function () {
    fakeGithub();
    $project = connectGithub();

    $this->getJson("/api/projects/{$project->id}/github")
        ->assertOk()
        ->assertJsonPath('lang', 'PHP')
        ->assertJsonPath('stars', 7)
        ->assertJsonPath('run.status', 'running')
        ->assertJsonStructure(['lang', 'stars', 'pushed_at', 'run' => ['status', 'at']]);
});

it('returns nulls for projects without a repo or unknown repos', function () {
    fakeGithub();
    connectGithub();
    $noRepo = Project::factory()->create(['repo' => null]);
    $unknown = Project::factory()->create(['repo' => 'yoq-repo']);

    $expected = ['lang' => null, 'stars' => null, 'pushed_at' => null, 'run' => null];

    $this->getJson("/api/projects/{$noRepo->id}/github")->assertOk()->assertExactJson($expected);
    $this->getJson("/api/projects/{$unknown->id}/github")->assertOk()->assertExactJson($expected);
});

it('returns 404 json for unknown projects', function () {
    $this->getJson('/api/projects/999/runs')->assertNotFound()->assertJsonStructure(['message']);
    $this->getJson('/api/projects/999/github')->assertNotFound();
    $this->getJson('/api/projects/abc/runs')->assertNotFound();
});

it('serves demo repo info when no username is set', function () {
    Http::fake();
    $project = Project::factory()->create(['repo' => 'crm-system']);

    $this->getJson("/api/projects/{$project->id}/github")->assertOk()->assertJsonPath('lang', 'PHP');
});

it('applies the public rate limit to external endpoints', function () {
    Http::fake();

    foreach (range(1, 120) as $_) {
        $this->getJson('/api/telegram')->assertOk();
    }

    $this->getJson('/api/telegram')->assertTooManyRequests();
});

it('remembers fresh values and serves stale ones after failures', function () {
    $cache = app(ResilientCache::class);
    $calls = 0;

    $value = $cache->remember('k', 60, 3600, function () use (&$calls) {
        $calls++;

        return ['ok' => true];
    });
    $cache->remember('k', 60, 3600, function () use (&$calls) {
        $calls++;

        return ['ok' => false];
    });

    expect($value)->toBe(['ok' => true])->and($calls)->toBe(1);

    Cache::forget('k:fresh');
    $stale = $cache->remember('k', 60, 3600, fn () => throw new RuntimeException('boom'));

    expect($stale)->toBe(['ok' => true]);
    expect(Cache::has('k:failed'))->toBeTrue();
});

it('returns null on failure when nothing was ever cached', function () {
    expect(app(ResilientCache::class)->remember('yangi', 60, 3600, fn () => throw new RuntimeException('boom')))->toBeNull();
});

it('settings telegram stays the manual source when the username is unset', function () {
    Setting::create(['key' => 'telegram', 'value' => ['u' => '', 'n' => 'Nom', 'posts' => []]]);

    $this->getJson('/api/telegram')->assertOk()->assertJsonPath('n', 'Nom');
});
