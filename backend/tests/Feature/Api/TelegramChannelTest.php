<?php

use App\Models\Setting;
use App\Services\TelegramChannel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function channelHtml(): string
{
    return file_get_contents(base_path('tests/Fixtures/telegram-channel.html'));
}

function setChannel(string $username = 'selfdev', array $manual = []): void
{
    Setting::create(['key' => 'telegram', 'value' => ['u' => $username, 'n' => 'Kanal', 'posts' => $manual]]);
}

it('extracts the last two text posts', function () {
    $posts = app(TelegramChannel::class)->parse(channelHtml());

    expect($posts)->toHaveCount(2)
        ->and(explode(' alert', $posts[0]['t'])[0])->toBe('Small steps every day & link')
        ->and($posts[1]['t'])->toBe('Learn in public')
        ->and($posts[1]['d'])->toBe('2026-11-04');
});

it('skips posts without text and joins lines', function () {
    $html = preg_replace('#<div class="tgme_widget_message_wrap"><div class="tgme_widget_message js-widget_message" data-post="selfdev/[34]">.*?</div></div></div></div></div>#s', '', channelHtml());

    $posts = app(TelegramChannel::class)->parse($html);

    expect(array_column(app(TelegramChannel::class)->parse(channelHtml()), 't'))->not->toContain('')
        ->and($posts[0]['t'])->toBe('Eng eski post ikkinchi qator');
});

it('truncates long text', function () {
    $posts = app(TelegramChannel::class)->parse(str_replace('Learn in public', str_repeat('soʻz ', 200), channelHtml()));

    expect(mb_strlen($posts[1]['t']))->toBeLessThanOrEqual(300)->and($posts[1]['t'])->toEndWith('…');
});

it('never requests with an invalid username', function () {
    Http::fake();

    foreach (['a', '../etc', 'user name', 'x?y=1', '1abc', str_repeat('a', 40)] as $bad) {
        expect(app(TelegramChannel::class)->latestPosts($bad))->toBeNull();
    }

    Http::assertNothingSent();
});

it('serves channel posts and caches them', function () {
    Http::fake(['t.me/s/selfdev' => Http::response(channelHtml())]);
    setChannel();

    $this->getJson('/api/telegram')
        ->assertOk()
        ->assertJsonPath('u', 'selfdev')
        ->assertJsonPath('n', 'Kanal')
        ->assertJsonPath('posts.1.t', 'Learn in public')
        ->assertJsonPath('posts.1.d', '2026-11-04')
        ->assertJsonStructure(['u', 'n', 'posts' => [['t', 'd']]]);
    $this->getJson('/api/telegram')->assertOk();

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request->url() === 'https://t.me/s/selfdev' && $request->hasHeader('User-Agent', 'personal-blog'));
});

it('falls back to manual posts when the channel fails', function () {
    Http::fake(['t.me/*' => Http::response('xato', 500)]);
    setChannel('selfdev', [['t' => 'Qoʻlda yozilgan post', 'd' => '2026-11-02']]);

    $this->getJson('/api/telegram')
        ->assertOk()
        ->assertJsonPath('posts', [['t' => 'Qoʻlda yozilgan post', 'd' => '2026-11-02']]);
});

it('keeps the previous result when the channel stops answering', function () {
    setChannel();
    Http::fake(['t.me/*' => Http::sequence()->push(channelHtml())->push('xato', 500)]);

    $this->getJson('/api/telegram')->assertJsonPath('posts.1.t', 'Learn in public');

    Cache::forget('telegram:selfdev:fresh');
    Cache::forget('telegram:selfdev:failed');

    $this->getJson('/api/telegram')->assertOk()->assertJsonPath('posts.1.t', 'Learn in public');
});

it('treats a page without posts as a failure', function () {
    Http::fake(['t.me/*' => Http::response('<html><body>Kanal topilmadi</body></html>')]);
    setChannel('selfdev', [['t' => 'Zaxira post', 'd' => '2026-11-02']]);

    $this->getJson('/api/telegram')->assertJsonPath('posts.0.t', 'Zaxira post');
});

it('uses only manual posts and sends nothing without a username', function () {
    Http::fake();
    setChannel('', [['t' => 'Faqat qoʻlda', 'd' => '2026-11-02'], ['t' => '  ', 'd' => '']]);

    $this->getJson('/api/telegram')
        ->assertOk()
        ->assertJsonPath('u', '')
        ->assertJsonCount(1, 'posts')
        ->assertJsonPath('posts.0.t', 'Faqat qoʻlda');
    Http::assertNothingSent();
});

it('returns an empty shape when nothing is configured', function () {
    Http::fake();

    $this->getJson('/api/telegram')->assertOk()->assertExactJson(['u' => '', 'n' => '', 'posts' => []]);
});

it('serves the channel feed newest first with full text and links', function () {
    Http::fake(['t.me/s/selfdev' => Http::response(channelHtml())]);
    setChannel();

    $this->getJson('/api/telegram/posts')
        ->assertOk()
        ->assertJsonPath('u', 'selfdev')
        ->assertJsonCount(3, 'posts')
        ->assertJsonPath('posts.0', ['id' => 4, 't' => 'Learn in public', 'd' => '2026-11-04', 'url' => 'https://t.me/selfdev/4'])
        ->assertJsonPath('posts.2.t', "Eng eski post\nikkinchi qator");
    $this->getJson('/api/telegram/posts')->assertOk();

    Http::assertSentCount(1);
});

it('returns an empty feed without a username or when the channel fails', function () {
    Http::fake(['t.me/*' => Http::response('xato', 500)]);

    $this->getJson('/api/telegram/posts')->assertOk()->assertExactJson(['u' => '', 'n' => '', 'posts' => []]);

    setChannel();

    $this->getJson('/api/telegram/posts')->assertOk()->assertJsonPath('posts', []);
});

it('uses the message own text and not the reply preview', function () {
    $html = '<div class="tgme_widget_message_wrap"><div class="tgme_widget_message js-widget_message" data-post="selfdev/7"><div class="tgme_widget_message_bubble"><a class="tgme_widget_message_reply"><div class="tgme_widget_message_text js-message_reply_text">Eski post matni</div></a><div class="tgme_widget_message_text js-message_text" dir="auto">Javob matni<br/>ikkinchi qator</div><div class="tgme_widget_message_footer"><a class="tgme_widget_message_date" href="https://t.me/selfdev/7"><time datetime="2026-11-05T10:00:00+00:00"></time></a></div></div></div></div>';

    $posts = app(TelegramChannel::class)->parseFeed($html, 'selfdev');

    expect($posts)->toHaveCount(1)->and($posts[0]['t'])->toBe("Javob matni\nikkinchi qator");
});

it('keeps long posts whole and reads older pages', function () {
    $long = str_repeat('uzun matn ', 600);
    $message = fn (int $id, string $text) => '<div class="tgme_widget_message_wrap"><div class="tgme_widget_message js-widget_message" data-post="selfdev/'.$id.'"><div class="tgme_widget_message_text js-message_text">'.$text.'</div><time datetime="2026-11-0'.($id % 9 + 1).'T10:00:00+00:00"></time></div></div>';
    Http::fake(function ($request) use ($message, $long) {
        return Http::response(str_contains($request->url(), 'before=5')
            ? '<html>'.$message(3, 'Eski').$message(1, 'Birinchi').'</html>'
            : '<html>'.$message(5, trim($long)).$message(8, 'Yangi').'</html>');
    });
    setChannel();

    $posts = $this->getJson('/api/telegram/posts')->assertOk()->json('posts');

    expect(array_column($posts, 'id'))->toBe([8, 5, 3, 1])
        ->and(mb_strlen($posts[1]['t']))->toBe(mb_strlen(trim($long)));
});
