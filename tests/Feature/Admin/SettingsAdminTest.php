<?php

namespace Tests\Feature\Admin;

use App\Services\SettingsService;
use Database\Seeders\SettingSeeder;

class SettingsAdminTest extends AdminTestCase
{
    private function payload(array $override = []): array
    {
        return $override + [
            'bio' => "Birinchi xatboshi\n\nIkkinchi",
            'open' => true,
            'github' => '',
            'now' => array_fill(0, 4, ['k' => '', 'v' => '']),
            'learn' => array_fill(0, 3, ['n' => '', 'p' => 0]),
            'telegram' => ['u' => '', 'n' => '', 'posts' => array_fill(0, 2, ['t' => '', 'd' => null])],
        ];
    }

    public function test_sahifa_bosh_va_toʻldirilgan_holatda_ochiladi(): void
    {
        $this->get('/admin/settings')->assertOk()->assertSee('Sayt va profil');

        $this->seed(SettingSeeder::class);

        $this->get('/admin/settings')->assertOk()->assertSee('Laravel events va PostgreSQL', false);
    }

    public function test_saqlash_va_normalizatsiya(): void
    {
        $response = $this->putJson('/admin/settings', $this->payload([
            'github' => ' https://github.com/utkirbek/repo ',
            'open' => false,
            'now' => [['k' => ' Oʻrganyapman ', 'v' => 'Laravel'], ['k' => '', 'v' => ''], ['k' => '', 'v' => ''], ['k' => '', 'v' => '']],
            'learn' => [['n' => 'Redis', 'p' => 72], ['n' => '', 'p' => ''], ['n' => '', 'p' => null]],
            'telegram' => ['u' => 'https://t.me/selfdev/12', 'n' => 'Kanal', 'posts' => [['t' => 'Post', 'd' => '2026-11-04'], ['t' => '', 'd' => null]]],
        ]))->assertOk();

        $settings = app(SettingsService::class);

        $this->assertSame('utkirbek', $settings->get('github'));
        $this->assertFalse($settings->get('open'));
        $this->assertSame('Oʻrganyapman', $settings->get('now')[0]['k']);
        $this->assertSame(['n' => 'Redis', 'p' => 72], $settings->get('learn')[0]);
        $this->assertSame(0, $settings->get('learn')[1]['p']);
        $this->assertSame('selfdev', $settings->get('telegram')['u']);
        $this->assertSame('selfdev', $response->json('settings.telegram.u'));
    }

    public function test_telegram_username_dan_at_olib_tashlanadi(): void
    {
        $this->putJson('/admin/settings', $this->payload(['telegram' => ['u' => '@selfdev', 'n' => '', 'posts' => array_fill(0, 2, ['t' => '', 'd' => null])]]))->assertOk();

        $this->assertSame('selfdev', app(SettingsService::class)->get('telegram')['u']);
    }

    public function test_validatsiya(): void
    {
        $this->putJson('/admin/settings', $this->payload(['learn' => [['n' => 'A', 'p' => 150], ['n' => '', 'p' => 0], ['n' => '', 'p' => 0]]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('learn.0.p');

        $this->putJson('/admin/settings', $this->payload(['now' => [['k' => '', 'v' => '']]]))->assertUnprocessable()->assertJsonValidationErrors('now');
        $this->putJson('/admin/settings', $this->payload(['telegram' => ['u' => '', 'n' => '', 'posts' => [['t' => '', 'd' => 'noto‘g‘ri'], ['t' => '', 'd' => null]]]]))->assertUnprocessable();
        $this->putJson('/admin/settings', $this->payload(['open' => 'xxx']))->assertUnprocessable();
    }

    public function test_sozlamalar_saytda_koʻrinadi(): void
    {
        $this->putJson('/admin/settings', $this->payload([
            'open' => false,
            'bio' => 'Mening bio matnim',
            'github' => 'utkirbek',
        ]))->assertOk();

        auth()->logout();

        $this->get('/men')->assertSee('Mening bio matnim')->assertSee('Hozircha band')->assertSee('https://github.com/utkirbek', false);
    }

    public function test_bio_xss_dan_himoyalangan_holda_saqlanadi_va_koʻrsatiladi(): void
    {
        $this->putJson('/admin/settings', $this->payload(['bio' => '<script>alert(1)</script>']))->assertOk();

        auth()->logout();

        $this->get('/men')->assertDontSee('<script>alert(1)</script>', false);
    }
}
