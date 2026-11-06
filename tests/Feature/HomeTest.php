<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Support\ActivityStats;
use App\Support\Uz;
use Carbon\Carbon;
use Database\Seeders\PostSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_bosh_sahifa_malumotsiz_ham_ochiladi(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Hali fikr yozilmagan')
            ->assertSee('Hali loyiha yoʻq')
            ->assertSee('Hali post qoʻshilmagan.');
    }

    public function test_bosh_sahifa_namuna_malumot_bilan_toliq_chiqadi(): void
    {
        $this->seed(PostSeeder::class);
        $this->seed(ProjectSeeder::class);
        $this->seed(SettingSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Nega fikrlarimni yozishga qaror qildim')
            ->assertSee('yana 5 ta fikr')
            ->assertSee('CRM tizimi')
            ->assertSee('Laravel events')
            ->assertSee('72%')
            ->assertSee('Self-development')
            ->assertSee('12–18');
    }

    public function test_songgi_fikr_qoralama_va_dev_yozuvlarni_oz_ichiga_olmaydi(): void
    {
        Post::factory()->create(['title' => 'Chop etilgan fikr', 'published_at' => '2026-01-01']);
        Post::factory()->draft()->create(['title' => 'Qoralama fikr', 'published_at' => '2026-02-01']);
        Post::factory()->dev()->create(['title' => 'Dasturlash yozuvi', 'published_at' => '2026-03-01']);

        $this->get('/')
            ->assertSee('Chop etilgan fikr')
            ->assertDontSee('Qoralama fikr')
            ->assertDontSee('Dasturlash yozuvi');
    }

    public function test_telegram_username_boyicha_havola_chiqadi(): void
    {
        Setting::create(['key' => 'telegram', 'value' => ['u' => 'selfdev', 'n' => 'Kanal', 'posts' => []]]);

        $this->get('/')
            ->assertSee('https://t.me/selfdev', false)
            ->assertSee('t.me/selfdev');
    }

    public function test_telegram_postlari_eskidan_yangiga_tartiblanadi(): void
    {
        Setting::create(['key' => 'telegram', 'value' => ['u' => '', 'n' => 'K', 'posts' => [
            ['t' => 'Yangi post', 'd' => '2026-11-04'],
            ['t' => 'Eski post', 'd' => '2026-11-01'],
            ['t' => '  ', 'd' => '2026-11-02'],
        ]]]);

        $this->get('/')->assertSeeInOrder(['Eski post', 'Yangi post']);
    }

    public function test_organish_bolimi_bosh_nomlarni_tashlab_yuboradi(): void
    {
        Setting::create(['key' => 'learn', 'value' => [['n' => 'Redis', 'p' => 130], ['n' => '', 'p' => 50]]]);

        $this->get('/')->assertSee('Redis')->assertSee('100%')->assertDontSee('50%');
    }

    public function test_loyiha_bosh_harflari(): void
    {
        $this->assertSame('CT', Uz::initials('CRM tizimi'));
        $this->assertSame('EB', Uz::initials('erp-brainup'));
        $this->assertSame('SS', Uz::initials('Shaxsiy sayt'));
        Project::factory()->create(['name' => 'Telegram vazifa bot']);

        $this->get('/')->assertSee('TV');
    }

    public function test_sana_formatlari(): void
    {
        $this->assertSame('5-noyabr, 2026', Uz::long('2026-11-05'));
        $this->assertSame('5 noy', Uz::short('2026-11-05'));
        $this->assertSame('', Uz::short(null));
    }

    public function test_oldin_formati(): void
    {
        Carbon::setTestNow('2026-11-06 12:00:00');

        $this->assertSame('hozirgina', Uz::ago(now()->subSeconds(10)));
        $this->assertSame('5 daqiqa oldin', Uz::ago(now()->subMinutes(5)));
        $this->assertSame('3 soat oldin', Uz::ago(now()->subHours(3)));
        $this->assertSame('2 kun oldin', Uz::ago(now()->subDays(2)));
        $this->assertSame('2 hafta oldin', Uz::ago(now()->subDays(15)));
        $this->assertSame('3 oy oldin', Uz::ago(now()->subDays(95)));
    }

    public function test_faollik_seriyasi_va_darajalari(): void
    {
        $days = [['d' => 'a', 'c' => 0], ['d' => 'b', 'c' => 3], ['d' => 'c', 'c' => 0], ['d' => 'd', 'c' => 4], ['d' => 'e', 'c' => 2], null];

        $this->assertSame(['total' => 9, 'streak' => 2], ActivityStats::summarize($days));
        $this->assertSame([0, 1, 2, 3, 4], array_map([ActivityStats::class, 'level'], [0, 2, 5, 9, 10]));
    }
}
