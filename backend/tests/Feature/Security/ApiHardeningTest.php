<?php

use App\Models\Post;
use App\Support\Markdown;

it('sets security headers on public api responses', function () {
    $this->getJson('/api/posts')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
});

it('never caches admin responses', function () {
    $this->actingAs(admin());

    expect($this->getJson('/api/admin/dashboard')->headers->get('Cache-Control'))->toContain('no-store');
    expect($this->getJson('/api/admin/me')->headers->get('Cache-Control'))->toContain('no-store');
});

it('sets security headers on error responses too', function () {
    $this->getJson('/api/posts/yoq')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('exempts the frontend server from the public rate limit', function () {
    config(['services.frontend.secret' => 'sir-kalit']);

    foreach (range(1, 130) as $_) {
        $this->getJson('/api/settings', ['X-Frontend-Secret' => 'sir-kalit'])->assertOk();
    }
});

it('rate limits clients with a wrong or missing frontend secret', function () {
    config(['services.frontend.secret' => 'sir-kalit']);

    foreach (range(1, 120) as $_) {
        $this->getJson('/api/settings', ['X-Frontend-Secret' => 'noto-gri'])->assertOk();
    }

    $this->getJson('/api/settings', ['X-Frontend-Secret' => 'noto-gri'])->assertTooManyRequests();
    $this->getJson('/api/settings')->assertTooManyRequests();
});

it('never exempts anyone when no secret is configured', function () {
    config(['services.frontend.secret' => null]);

    foreach (range(1, 120) as $_) {
        $this->getJson('/api/settings', ['X-Frontend-Secret' => ''])->assertOk();
    }

    $this->getJson('/api/settings', ['X-Frontend-Secret' => ''])->assertTooManyRequests();
});

it('keeps the login endpoint limited even with the frontend secret', function () {
    config(['services.frontend.secret' => 'sir-kalit']);
    $this->withHeaders(['Origin' => 'http://localhost:3000', 'X-Frontend-Secret' => 'sir-kalit']);

    foreach (range(1, 5) as $_) {
        $this->postJson('/api/admin/login', ['password' => 'xato'])->assertUnprocessable();
    }

    $this->postJson('/api/admin/login', ['password' => 'xato'])->assertTooManyRequests();
});

it('refuses writes to public content without authentication', function () {
    $post = Post::factory()->create();

    $this->patchJson("/api/admin/posts/{$post->id}", ['title' => 'Buzildi'])->assertUnauthorized();
    $this->deleteJson("/api/admin/posts/{$post->id}")->assertUnauthorized();
    $this->postJson('/api/admin/uploads')->assertUnauthorized();
    $this->putJson('/api/admin/settings', [])->assertUnauthorized();

    expect($post->fresh()->title)->not->toBe('Buzildi');
});

it('ignores mass-assigned protected fields on autosave', function () {
    $this->actingAs(admin());
    $post = Post::factory()->draft()->create();

    $this->patchJson("/api/admin/posts/{$post->id}", ['title' => 'Nom', 'status' => 'published', 'id' => 999, 'pending' => ['x' => 1]])->assertOk();

    expect($post->fresh()->status->value)->toBe('draft')->and($post->fresh()->id)->toBe($post->id);
});

it('neutralizes advanced xss payloads in markdown', function (string $payload, array $forbidden) {
    $html = Markdown::render($payload)['html'];

    foreach ($forbidden as $needle) {
        expect(mb_strtolower($html))->not->toContain($needle);
    }
})->with([
    'attribute breakout in link' => ['[x](/p" onmouseover="alert(1))', ['" onmouseover']],
    'attribute breakout in image' => ['![a](https://x.uz/i.png"onerror="alert(1))', ['"onerror']],
    'svg onload' => ['<svg onload=alert(1)>', ['<svg']],
    'iframe' => ['<iframe src="https://evil.uz"></iframe>', ['<iframe']],
    'uppercase scheme' => ['[x](JAVASCRIPT:alert(1))', ['href="javascript', 'javascript:']],
    'entity encoded scheme' => ['[x](&#106;avascript:alert(1))', ['href="&#106;', 'href="javascript']],
    'vbscript' => ['[x](vbscript:msgbox(1))', ['vbscript:']],
    'data uri image' => ['![x](data:image/svg+xml;base64,AAA)', ['data:image', '<img']],
    'protocol relative' => ['[x](//evil.uz/p)', ['//evil.uz']],
    'script in heading' => ['## <script>alert(1)</script>', ['<script']],
    'script in code info string' => ["```\"><script>alert(1)</script>\nx\n```", ['<script']],
    'html comment' => ['<!-- <script>alert(1)</script> -->', ['<script']],
]);
