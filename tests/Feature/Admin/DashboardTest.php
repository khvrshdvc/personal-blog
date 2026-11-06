<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Setting;
use Database\Seeders\PostSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;

class DashboardTest extends AdminTestCase
{
    public function test_bosh_holatda_dashboard_ochiladi(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Birinchi yozuvingizni yozing')
            ->assertSee('Sayt holati');
    }

    public function test_dashboard_malumotlar_bilan_toladi(): void
    {
        $this->seed(PostSeeder::class);
        $this->seed(ProjectSeeder::class);
        $this->seed(SettingSeeder::class);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Oxirgi tahrir')
            ->assertSee('6 fikr · 5 dasturlash')
            ->assertSee('CRM tizimi')
            ->assertSee('Davom ettirish')
            ->assertSee('Toshkent');
    }

    public function test_dashboard_kutilayotgan_ozgarishni_hisoblaydi(): void
    {
        $post = Post::factory()->create();
        $post->update(['pending' => $post->fields()]);

        $html = $this->get('/admin')->getContent();

        $this->assertMatchesRegularExpression('/Oʻzgarish<\/span>\s*<b>1<\/b>/u', $html);
    }

    public function test_sayt_holati_sozlamalarga_qarab_ozgaradi(): void
    {
        Setting::create(['key' => 'github', 'value' => 'utkirbek']);
        Setting::create(['key' => 'open', 'value' => false]);

        $this->get('/admin')->assertSee('@utkirbek')->assertSee('Oʻchirilgan');
    }
}
