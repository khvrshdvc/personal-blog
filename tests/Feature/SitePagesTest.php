<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    private function seedAll(): void
    {
        $this->seed(PostSeeder::class);
        $this->seed(ProjectSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    public function test_fikrlar_sahifasi_faqat_chop_etilgan_fikrlarni_korsatadi(): void
    {
        $this->seedAll();

        $this->get('/fikrlar')
            ->assertOk()
            ->assertSee('6 ta yozuv')
            ->assertSee('Mustaqil oʻrganishning eng qiyin joyi')
            ->assertDontSee('Ikigai')
            ->assertDontSee('Laravel events: post yaratilganda');
    }

    public function test_fikrlar_kartalari_filtr_atributiga_ega(): void
    {
        $this->seedAll();

        $this->get('/fikrlar')
            ->assertSee("x-show=\"shown('bolalarga-dars-berish', 'Ustozlik')\"", false)
            ->assertSee('x-bind:class="{ big: featured }"', false);
    }

    public function test_fikrlar_mavzu_tugmalari_faqat_mavjud_mavzular(): void
    {
        Post::factory()->create(['tag' => 'Hayot']);

        $this->get('/fikrlar')->assertSee('Hayot')->assertDontSee('Ustozlik');
    }

    public function test_fikrlar_bosh_holat(): void
    {
        $this->get('/fikrlar')->assertOk()->assertSee('Hali fikr yozilmagan.');
    }

    public function test_fikrlar_qidiruvi_slug_qaytaradi(): void
    {
        Post::factory()->create(['slug' => 'redis', 'title' => 'Redis haqida']);
        Post::factory()->create(['slug' => 'boshqa', 'title' => 'Boshqa', 'body' => 'ichida kesh bor']);
        Post::factory()->dev()->create(['slug' => 'dev-redis', 'title' => 'Redis dev']);
        Post::factory()->draft()->create(['slug' => 'qoralama', 'title' => 'Redis qoralama']);

        $this->getJson('/fikrlar/qidiruv?q=redis')->assertOk()->assertExactJson(['redis']);
        $this->getJson('/fikrlar/qidiruv?q=kesh')->assertExactJson(['boshqa']);
        $this->getJson('/fikrlar/qidiruv?q=')->assertExactJson([]);
    }

    public function test_dasturlash_yozuvlar_boligi(): void
    {
        $this->seedAll();

        foreach (['/dasturlash', '/yozuvlar'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Laravel Sail bilan Docker muhitini sozlash')
                ->assertDontSee('Laravelʼda queue bilan ishlash')
                ->assertDontSee('Nega fikrlarimni yozishga qaror qildim');
        }
    }

    public function test_dasturlash_loyihalar_boligi(): void
    {
        $this->seedAll();

        $this->get('/loyihalar')
            ->assertOk()
            ->assertSee('CRM tizimi')
            ->assertSee('Bu namuna')
            ->assertSee('Maʼlumotlar bazasi')
            ->assertSee('class="on"', false);
    }

    public function test_stack_faqat_butun_soz_boyicha_yoritiladi(): void
    {
        Setting::create(['key' => 'learn', 'value' => [['n' => 'Laravel events', 'p' => 10], ['n' => 'Click va Payme', 'p' => 5]]]);

        $html = $this->get('/loyihalar')->getContent();

        $this->assertStringContainsString('<span class="on">Laravel</span>', $html);
        $this->assertStringNotContainsString('<span class="on">C</span>', $html);
        $this->assertStringNotContainsString('<span class="on">Redis</span>', $html);
    }

    public function test_dasturlash_loyihalar_bosh_holat(): void
    {
        $this->get('/loyihalar')->assertOk()->assertSee('Hali loyiha qoʻshilmagan.');
    }

    public function test_dasturlash_faollik_boligi(): void
    {
        $this->get('/faollik')
            ->assertOk()
            ->assertSee('Namuna maʼlumot')
            ->assertSee('GitHub Actions')
            ->assertSee('kunlik seriya')
            ->assertSee('hm-grid', false);
    }

    public function test_dasturlash_bolimlari_orasida_faol_tab_belgilanadi(): void
    {
        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('loyihalar'), '#').'"\s+aria-current="page"#', $this->get('/loyihalar')->getContent());
        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('faollik'), '#').'"\s+aria-current="page"#', $this->get('/faollik')->getContent());
        $this->assertDoesNotMatchRegularExpression('#href="'.preg_quote(route('faollik'), '#').'"\s+aria-current="page"#', $this->get('/loyihalar')->getContent());
    }

    public function test_men_sahifasi_sozlamalardan_toladi(): void
    {
        $this->seedAll();

        $this->get('/men')
            ->assertOk()
            ->assertSee('Dasturlashni mustaqil oʻrganyapman')
            ->assertSee('Laravel events va PostgreSQL')
            ->assertSee('Loyihalarim')
            ->assertSee('Admin panel');
    }

    public function test_men_sahifasida_github_va_telegram_havolalari(): void
    {
        Setting::create(['key' => 'github', 'value' => 'utkirbek']);
        Setting::create(['key' => 'telegram', 'value' => ['u' => 'selfdev', 'n' => '', 'posts' => []]]);

        $this->get('/men')
            ->assertSee('https://github.com/utkirbek', false)
            ->assertSee('https://t.me/selfdev', false);
    }

    public function test_men_sahifasida_bio_xss_dan_himoyalangan(): void
    {
        Setting::create(['key' => 'bio', 'value' => "<script>alert(1)</script>\n\nIkkinchi"]);

        $this->get('/men')->assertDontSee('<script>alert(1)</script>', false)->assertSee('Ikkinchi');
    }

    public function test_yozuv_sahifasi_ochiladi(): void
    {
        $this->seedAll();

        $this->get('/post/mustaqil-organish')
            ->assertOk()
            ->assertSee('Mustaqil oʻrganishning eng qiyin joyi')
            ->assertSee('<title>Mustaqil oʻrganishning eng qiyin joyi — Utkirbek</title>', false)
            ->assertSee('daqiqa oʻqish')
            ->assertSee('<h2 id="menga-yordam-bergan-uch-narsa">', false);
    }

    public function test_fikr_yozuvida_mundarija_yoq_dev_yozuvida_bor(): void
    {
        $this->seedAll();

        $this->get('/post/mustaqil-organish')->assertDontSee('Mundarija');
        $this->get('/post/laravel-events')->assertSee('Mundarija')->assertSee('2 boʻlim');
    }

    public function test_navigatsiyada_yozuv_kategoriyasi_faol(): void
    {
        $this->seedAll();

        $this->get('/post/laravel-events')->assertSee('class="c tn on"', false);
        $html = $this->get('/post/laravel-events')->getContent();
        $this->assertMatchesRegularExpression('/class="c tn on"[^>]*>\s*<span class="tn-t">.*?<b>Dasturlash<\/b>/s', $html);
    }

    public function test_qoralama_mehmonga_404_adminga_korinadi(): void
    {
        $this->seedAll();

        $this->get('/post/ikigai')->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get('/post/ikigai')
            ->assertOk()
            ->assertSee('Qoralama — buni faqat siz koʻryapsiz')
            ->assertSee('Tahrirlash');
    }

    public function test_chop_etilgan_yozuvda_tahrirlash_tugmasi_mehmonga_korinmaydi(): void
    {
        $this->seedAll();

        $this->get('/post/laravel-events')->assertDontSee('Tahrirlash');
    }

    public function test_mavjud_bolmagan_yozuv_404_sahifasi(): void
    {
        $this->get('/post/yoq-yozuv')
            ->assertNotFound()
            ->assertSee('Sahifa topilmadi')
            ->assertSee('Bosh sahifaga');
    }

    public function test_oldingi_va_keyingi_yozuvlar(): void
    {
        $a = Post::factory()->create(['title' => 'Eng eski', 'published_at' => '2026-01-01']);
        $b = Post::factory()->create(['title' => 'Oʻrtadagi', 'published_at' => '2026-02-01']);
        $c = Post::factory()->create(['title' => 'Eng yangi', 'published_at' => '2026-03-01']);
        Post::factory()->dev()->create(['title' => 'Boshqa kategoriya', 'published_at' => '2026-02-15']);

        $html = $this->get('/post/'.$b->slug)->getContent();

        $this->assertMatchesRegularExpression('/Keyingi.*?Eng yangi/s', $html);
        $this->assertMatchesRegularExpression('/Oldingi.*?Eng eski/s', $html);
        $this->assertStringNotContainsString('Boshqa kategoriya', $html);

        $this->get('/post/'.$c->slug)->assertDontSee('Keyingi')->assertSee('Oldingi');
        $this->get('/post/'.$a->slug)->assertSee('Keyingi')->assertDontSee('Oldingi');
    }

    public function test_bir_kunlik_yozuvlar_id_boyicha_tartiblanadi(): void
    {
        $first = Post::factory()->create(['title' => 'Birinchi', 'published_at' => '2026-05-01']);
        $second = Post::factory()->create(['title' => 'Ikkinchi', 'published_at' => '2026-05-01']);

        $this->get('/post/'.$first->slug)->assertSee('Ikkinchi');
        $this->get('/post/'.$second->slug)->assertSee('Birinchi');
    }

    public function test_yozuv_matnidagi_xavfli_kod_ekranlanadi(): void
    {
        $post = Post::factory()->create(['body' => "<script>alert(1)</script>\n\n[x](javascript:alert(1))"]);

        $this->get('/post/'.$post->slug)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:', false);
    }

    public function test_muqova_url_xavfsiz(): void
    {
        $this->assertSame('https://a.uz/i.png', Post::factory()->make(['cover' => 'https://a.uz/i.png'])->coverUrl());
        $this->assertSame(asset('storage/covers/a.png'), Post::factory()->make(['cover' => 'covers/a.png'])->coverUrl());
        $this->assertSame(asset('storage/javascript:alert(1)'), Post::factory()->make(['cover' => 'javascript:alert(1)'])->coverUrl());
        $this->assertNull(Post::factory()->make(['cover' => null])->coverUrl());
    }

    public function test_loyiha_stack_va_demo_havolasi(): void
    {
        Project::factory()->create(['name' => 'Sinov', 'demo_url' => 'https://demo.uz', 'stack' => ['Laravel', 'Redis']]);

        $this->get('/loyihalar')
            ->assertSee('Laravel · Redis')
            ->assertSee('href="https://demo.uz"', false);
    }
}
