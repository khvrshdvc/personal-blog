<?php

use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function repo(array $override = []): array
{
    return $override + [
        'name' => 'crm-system',
        'full_name' => 'utkirbek/crm-system',
        'description' => 'Mijozlar bilan ishlash tizimi',
        'language' => 'PHP',
        'topics' => ['laravel', 'mysql'],
        'homepage' => 'https://crm.uz',
        'fork' => false,
        'archived' => false,
        'stargazers_count' => 3,
    ];
}

function fakeRepos(array $repos, int $status = 200): void
{
    Http::fake(['api.github.com/users/utkirbek/repos*' => Http::response($repos, $status)]);
}

beforeEach(function () {
    $this->actingAs(admin());
    Setting::create(['key' => 'github', 'value' => 'utkirbek']);
});

it('imports public repos as projects with mapped fields', function () {
    fakeRepos([repo()]);

    $this->postJson('/api/admin/projects/sync')
        ->assertOk()
        ->assertJson(['created' => 1, 'updated' => 0, 'total' => 1]);

    $project = Project::first();

    expect($project->name)->toBe('crm-system')
        ->and($project->repo)->toBe('utkirbek/crm-system')
        ->and($project->description)->toBe('Mijozlar bilan ishlash tizimi')
        ->and($project->stack)->toBe(['PHP', 'Laravel', 'Mysql'])
        ->and($project->demo_url)->toBe('https://crm.uz');
});

it('skips forks, archived repos and the profile repo', function () {
    fakeRepos([
        repo(['name' => 'forklangan', 'full_name' => 'utkirbek/forklangan', 'fork' => true]),
        repo(['name' => 'arxiv', 'full_name' => 'utkirbek/arxiv', 'archived' => true]),
        repo(['name' => 'Utkirbek', 'full_name' => 'utkirbek/utkirbek']),
        repo(['name' => 'yaxshi', 'full_name' => 'utkirbek/yaxshi']),
    ]);

    $this->postJson('/api/admin/projects/sync')->assertOk()->assertJson(['created' => 1]);

    expect(Project::pluck('name')->all())->toBe(['yaxshi']);
});

it('is idempotent and keeps manual edits', function () {
    fakeRepos([repo()]);
    $this->postJson('/api/admin/projects/sync')->assertOk();
    Project::first()->update(['name' => 'CRM tizimi', 'description' => 'Mening tavsifim']);

    $this->postJson('/api/admin/projects/sync')->assertOk()->assertJson(['created' => 0, 'updated' => 0, 'total' => 1]);

    expect(Project::first()->only(['name', 'description']))->toBe(['name' => 'CRM tizimi', 'description' => 'Mening tavsifim']);
});

it('backfills only empty fields of an existing project', function () {
    Project::factory()->create(['name' => 'CRM', 'repo' => 'crm-system', 'description' => null, 'stack' => [], 'demo_url' => null]);
    fakeRepos([repo()]);

    $this->postJson('/api/admin/projects/sync')->assertOk()->assertJson(['created' => 0, 'updated' => 1]);

    $project = Project::first();

    expect($project->name)->toBe('CRM')
        ->and($project->description)->toBe('Mijozlar bilan ishlash tizimi')
        ->and($project->stack)->toBe(['PHP', 'Laravel', 'Mysql'])
        ->and($project->demo_url)->toBe('https://crm.uz');
});

it('recognizes existing projects stored as owner/repo regardless of case', function () {
    Project::factory()->create(['repo' => 'Utkirbek/CRM-System']);
    fakeRepos([repo()]);

    $this->postJson('/api/admin/projects/sync')->assertOk()->assertJson(['created' => 0]);

    expect(Project::count())->toBe(1);
});

it('does not bring back a project the admin deleted', function () {
    fakeRepos([repo()]);
    $this->postJson('/api/admin/projects/sync')->assertOk();
    $id = Project::first()->id;

    $this->deleteJson("/api/admin/projects/{$id}")->assertNoContent();
    $this->postJson('/api/admin/projects/sync')->assertOk()->assertJson(['created' => 0, 'total' => 0]);

    expect(Project::count())->toBe(0);
});

it('imports a deleted repo again after the admin re-adds it manually', function () {
    fakeRepos([repo()]);
    $this->postJson('/api/admin/projects/sync')->assertOk();
    $this->deleteJson('/api/admin/projects/'.Project::first()->id)->assertNoContent();

    $this->postJson('/api/admin/projects', ['name' => 'CRM', 'repo' => 'utkirbek/crm-system'])->assertCreated();

    expect((array) Setting::find('github_hidden')->value)->not->toContain('crm-system');
});

it('normalizes the homepage and drops invalid ones', function () {
    fakeRepos([
        repo(['name' => 'bir', 'full_name' => 'utkirbek/bir', 'homepage' => 'bir.uz']),
        repo(['name' => 'ikki', 'full_name' => 'utkirbek/ikki', 'homepage' => 'javascript:alert(1)']),
        repo(['name' => 'uch', 'full_name' => 'utkirbek/uch', 'homepage' => '']),
    ]);

    $this->postJson('/api/admin/projects/sync')->assertOk();

    expect(Project::orderBy('id')->pluck('demo_url', 'name')->all())->toBe(['bir' => 'https://bir.uz', 'ikki' => null, 'uch' => null]);
});

it('limits description length and stack size', function () {
    fakeRepos([repo(['description' => str_repeat('a', 400), 'topics' => range(1, 20)])]);

    $this->postJson('/api/admin/projects/sync')->assertOk();

    expect(mb_strlen(Project::first()->description))->toBe(220)
        ->and(Project::first()->stack)->toHaveCount(8);
});

it('requires a valid github username', function () {
    Setting::find('github')->update(['value' => '']);
    Http::fake();

    $this->postJson('/api/admin/projects/sync')->assertUnprocessable()->assertJsonValidationErrors('github');
    Http::assertNothingSent();

    Setting::find('github')->update(['value' => '../etc']);
    $this->postJson('/api/admin/projects/sync')->assertUnprocessable();
    Http::assertNothingSent();
});

it('answers 502 when GitHub fails and changes nothing', function () {
    fakeRepos([], 500);
    Project::factory()->create();

    $this->postJson('/api/admin/projects/sync')->assertStatus(502)->assertJsonStructure(['message']);

    expect(Project::count())->toBe(1);
});

it('syncs automatically when the github username changes in settings', function () {
    Setting::find('github')->update(['value' => '']);
    fakeRepos([repo()]);

    $this->putJson('/api/admin/settings', [
        'bio' => '',
        'open' => true,
        'github' => 'https://github.com/utkirbek',
        'now' => array_fill(0, 4, ['k' => '', 'v' => '']),
        'learn' => array_fill(0, 3, ['n' => '', 'p' => 0]),
        'telegram' => ['u' => '', 'n' => '', 'posts' => array_fill(0, 2, ['t' => '', 'd' => ''])],
    ])->assertOk();

    expect(Project::pluck('name')->all())->toBe(['crm-system']);
});

it('does not sync when the username is unchanged', function () {
    Http::fake();

    $this->putJson('/api/admin/settings', [
        'bio' => 'x',
        'open' => true,
        'github' => 'utkirbek',
        'now' => array_fill(0, 4, ['k' => '', 'v' => '']),
        'learn' => array_fill(0, 3, ['n' => '', 'p' => 0]),
        'telegram' => ['u' => '', 'n' => '', 'posts' => array_fill(0, 2, ['t' => '', 'd' => ''])],
    ])->assertOk();

    Http::assertNothingSent();
});

it('saves settings even when the automatic sync fails', function () {
    Log::spy();
    Setting::find('github')->update(['value' => '']);
    fakeRepos([], 500);

    $this->putJson('/api/admin/settings', [
        'bio' => '',
        'open' => true,
        'github' => 'utkirbek',
        'now' => array_fill(0, 4, ['k' => '', 'v' => '']),
        'learn' => array_fill(0, 3, ['n' => '', 'p' => 0]),
        'telegram' => ['u' => '', 'n' => '', 'posts' => array_fill(0, 2, ['t' => '', 'd' => ''])],
    ])->assertOk()->assertJsonPath('github', 'utkirbek');

    expect(Setting::find('github')->value)->toBe('utkirbek');
    Log::shouldHaveReceived('warning');
});
