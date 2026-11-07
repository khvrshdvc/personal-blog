<?php

use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

function fakeGithub(array $override = []): void
{
    $today = now('UTC')->toDateString();

    Http::fake($override + [
        'api.github.com/users/utkirbek' => Http::response(['login' => 'Utkirbek']),
        'api.github.com/users/Utkirbek/repos*' => Http::response([
            ['name' => 'crm-system', 'language' => 'PHP', 'stargazers_count' => 7, 'pushed_at' => now()->subHours(3)->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system'],
        ]),
        'api.github.com/repos/Utkirbek/crm-system/actions/runs*' => Http::response(['workflow_runs' => [
            ['name' => 'Tests', 'display_title' => "fix: summa\nqo'shimcha", 'head_branch' => 'main', 'run_number' => 12, 'status' => 'completed', 'conclusion' => 'failure', 'created_at' => now()->subMinutes(30)->toIso8601String(), 'run_started_at' => now()->subMinutes(30)->toIso8601String(), 'updated_at' => now()->subMinutes(28)->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system/actions/runs/1'],
            ['name' => 'Deploy', 'display_title' => 'feat: yangi', 'head_branch' => 'main', 'run_number' => 13, 'status' => 'in_progress', 'conclusion' => null, 'created_at' => now()->subMinutes(5)->toIso8601String(), 'updated_at' => now()->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system/actions/runs/2'],
        ]]),
        'api.github.com/graphql' => Http::response(['data' => ['user' => ['contributionsCollection' => ['contributionCalendar' => ['weeks' => [
            ['contributionDays' => [['date' => $today, 'contributionCount' => 5]]],
        ]]]]]]),
        'api.github.com/users/Utkirbek/events/public*' => Http::response([
            ['type' => 'PushEvent', 'created_at' => now()->toIso8601String(), 'payload' => ['size' => 3]],
            ['type' => 'IssuesEvent', 'created_at' => now()->toIso8601String(), 'payload' => []],
        ]),
    ]);
}

function connectGithub(string $username = 'utkirbek'): Project
{
    Setting::create(['key' => 'github', 'value' => $username]);
    Project::factory()->create(['repo' => null]);

    return Project::factory()->create(['repo' => 'crm-system']);
}
