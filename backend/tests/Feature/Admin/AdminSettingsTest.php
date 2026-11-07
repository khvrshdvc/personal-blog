<?php

use Database\Seeders\SettingSeeder;

beforeEach(function () {
    $this->actingAs(admin());
    $this->seed(SettingSeeder::class);
});

function validSettings(array $override = []): array
{
    return array_replace_recursive([
        'bio' => 'Salom',
        'open' => false,
        'github' => 'https://github.com/utkirbek',
        'now' => array_fill(0, 4, ['k' => 'Kalit', 'v' => 'Qiymat']),
        'learn' => array_fill(0, 3, ['n' => 'Mavzu', 'p' => 50]),
        'telegram' => [
            'u' => '@kanal',
            'n' => 'Kanal',
            'posts' => array_fill(0, 2, ['t' => 'Post', 'd' => '2026-11-01']),
        ],
    ], $override);
}

it('returns all settings', function () {
    $this->getJson('/api/admin/settings')
        ->assertOk()
        ->assertJsonStructure(['github', 'open', 'bio', 'now', 'learn', 'telegram'])
        ->assertJsonCount(4, 'now');
});

it('updates settings and normalizes usernames', function () {
    $this->putJson('/api/admin/settings', validSettings())
        ->assertOk()
        ->assertJsonPath('github', 'utkirbek')
        ->assertJsonPath('telegram.u', 'kanal')
        ->assertJsonPath('open', false);

    $this->getJson('/api/settings')->assertJsonPath('bio', 'Salom')->assertJsonPath('open', false);
});

it('clamps learn percentages through validation', function () {
    $this->putJson('/api/admin/settings', validSettings(['learn' => [['p' => 101]]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('learn.0.p');
});

it('enforces the fixed list sizes', function () {
    $payload = validSettings();
    $payload['now'] = array_fill(0, 3, ['k' => 'a', 'v' => 'b']);
    $this->putJson('/api/admin/settings', $payload)->assertUnprocessable()->assertJsonValidationErrors('now');

    $payload = validSettings();
    $payload['learn'] = array_fill(0, 2, ['n' => 'a', 'p' => 1]);
    $this->putJson('/api/admin/settings', $payload)->assertUnprocessable()->assertJsonValidationErrors('learn');

    $payload = validSettings();
    $payload['telegram']['posts'] = [['t' => 'a', 'd' => '2026-11-01']];
    $this->putJson('/api/admin/settings', $payload)->assertUnprocessable()->assertJsonValidationErrors('telegram.posts');
});

it('requires the open flag and valid telegram dates', function () {
    $payload = validSettings();
    unset($payload['open']);
    $this->putJson('/api/admin/settings', $payload)->assertUnprocessable()->assertJsonValidationErrors('open');

    $this->putJson('/api/admin/settings', validSettings(['telegram' => ['posts' => [['d' => 'kecha']]]]))
        ->assertUnprocessable();
});
