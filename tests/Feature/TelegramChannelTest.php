<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\TelegramChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramChannelTest extends TestCase
{
    use RefreshDatabase;

    private function html(): string
    {
        return file_get_contents(base_path('tests/Fixtures/telegram-channel.html'));
    }

    private function channel(string $username = 'selfdev', array $manual = []): void
    {
        Setting::create(['key' => 'telegram', 'value' => ['u' => $username, 'n' => 'Kanal', 'posts' => $manual]]);
    }

    public function test_songgi_ikki_matnli_post_ajratib_olinadi(): void
    {
        $posts = app(TelegramChannel::class)->parse($this->html());

        $this->assertCount(2, $posts);
        $this->assertSame('Small steps every day & link', explode(' alert', $posts[0]['t'])[0]);
        $this->assertSame('Learn in public', $posts[1]['t']);
        $this->assertSame('2026-11-04', $posts[1]['d']);
    }

    public function test_matnsiz_postlar_tashlab_yuboriladi_va_qatorlar_birlashtiriladi(): void
    {
        $all = app(TelegramChannel::class)->parse(str_replace('tgme_widget_message_wrap', 'tgme_widget_message_wrap', $this->html()));
        $this->assertNotContains('', array_column($all, 't'));

        $html = preg_replace('#<div class="tgme_widget_message_wrap"><div class="tgme_widget_message js-widget_message" data-post="selfdev/[34]">.*?</div></div></div></div></div>#s', '', $this->html());
        $posts = app(TelegramChannel::class)->parse($html);

        $this->assertSame('Eng eski post ikkinchi qator', $posts[0]['t']);
    }

    public function test_uzun_matn_qisqartiriladi(): void
    {
        $html = str_replace('Learn in public', str_repeat('soʻz ', 200), $this->html());

        $posts = app(TelegramChannel::class)->parse($html);

        $this->assertLessThanOrEqual(300, mb_strlen($posts[1]['t']));
        $this->assertStringEndsWith('…', $posts[1]['t']);
    }

    public function test_notoʻgʻri_username_soʻrov_yubormaydi(): void
    {
        Http::fake();

        foreach (['a', '../etc', 'user name', 'x?y=1', '1abc', str_repeat('a', 40)] as $bad) {
            $this->assertNull(app(TelegramChannel::class)->latestPosts($bad));
        }

        Http::assertNothingSent();
    }

    public function test_bosh_sahifa_kanal_postlarini_koʻrsatadi_va_keshlaydi(): void
    {
        Http::fake(['t.me/s/selfdev' => Http::response($this->html())]);
        $this->channel();

        $this->get('/')->assertOk()->assertSee('Learn in public')->assertSee('4 noy');
        $this->get('/')->assertOk();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->url() === 'https://t.me/s/selfdev' && $request->hasHeader('User-Agent', 'personal-blog'));
    }

    public function test_kanal_javob_bermasa_qoʻlda_yozilgan_postlar_chiqadi(): void
    {
        Http::fake(['t.me/*' => Http::response('xato', 500)]);
        $this->channel('selfdev', [['t' => 'Qoʻlda yozilgan post', 'd' => '2026-11-02']]);

        $this->get('/')->assertOk()->assertSee('Qoʻlda yozilgan post');
    }

    public function test_kanal_javob_bermasa_oldingi_natija_saqlanib_qoladi(): void
    {
        $this->channel();
        Http::fake(['t.me/*' => Http::sequence()->push($this->html())->push('xato', 500)]);

        $this->get('/')->assertSee('Learn in public');

        Cache::forget('telegram:selfdev:fresh');
        Cache::forget('telegram:selfdev:failed');

        $this->get('/')->assertOk()->assertSee('Learn in public');
    }

    public function test_bosh_html_postlarsiz_natija_xato_hisoblanadi(): void
    {
        Http::fake(['t.me/*' => Http::response('<html><body>Kanal topilmadi</body></html>')]);
        $this->channel('selfdev', [['t' => 'Zaxira post', 'd' => '2026-11-02']]);

        $this->get('/')->assertSee('Zaxira post');
    }

    public function test_username_boʻlmasa_tashqi_soʻrov_yoʻq(): void
    {
        Http::fake();
        $this->channel('', [['t' => 'Faqat qoʻlda', 'd' => '2026-11-02']]);

        $this->get('/')->assertSee('Faqat qoʻlda');

        Http::assertNothingSent();
    }
}
