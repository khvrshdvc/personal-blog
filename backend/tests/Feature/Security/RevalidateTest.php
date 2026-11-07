<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Services\FrontendRevalidator;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config(['services.frontend.url' => 'http://frontend.test', 'services.frontend.secret' => 'sir-kalit']);
    $this->actingAs(admin());
});

function frontend(int $status = 200): void
{
    Http::fake(['frontend.test/*' => Http::response(['revalidated' => []], $status)]);
}

function ready(): void
{
    app(FrontendRevalidator::class)->flush();
    $GLOBALS['revalidate_offset'] = count(Http::recorded());
}

function sentTags(): array
{
    return Http::recorded()
        ->slice($GLOBALS['revalidate_offset'] ?? 0)
        ->filter(fn (array $pair) => $pair[0]->url() === 'http://frontend.test/api/revalidate')
        ->map(fn (array $pair) => $pair[0]->data()['tags'])
        ->values()
        ->all();
}

it('revalidates posts and the post tag on publish', function () {
    frontend();
    $post = Post::factory()->draft()->create(['title' => 'Tayyor', 'slug' => 'tayyor', 'body' => 'matn']);
    ready();

    $this->postJson("/api/admin/posts/{$post->id}/publish")->assertOk();

    expect(sentTags())->toHaveCount(1)->and(sentTags()[0])->toEqualCanonicalizing(['posts', 'post:tayyor']);
    Http::assertSent(fn (Request $request) => $request->url() === 'http://frontend.test/api/revalidate'
        && $request['secret'] === 'sir-kalit'
        && $request->hasHeader('Accept', 'application/json'));
});

it('revalidates both the old and the new slug when applying a rename', function () {
    frontend();
    $post = Post::factory()->create(['slug' => 'eski']);
    ready();
    $this->patchJson("/api/admin/posts/{$post->id}", ['slug' => 'yangi'])->assertOk();
    expect(sentTags())->toBeEmpty();

    $this->postJson("/api/admin/posts/{$post->id}/apply")->assertOk();

    expect(sentTags())->toHaveCount(1)->and(sentTags()[0])->toEqualCanonicalizing(['posts', 'post:eski', 'post:yangi']);
});

it('revalidates on unpublish and on deleting a published post', function () {
    frontend();
    $post = Post::factory()->create(['slug' => 'bir']);
    $other = Post::factory()->create(['slug' => 'ikki']);
    ready();

    $this->postJson("/api/admin/posts/{$post->id}/unpublish")->assertOk();
    $this->deleteJson("/api/admin/posts/{$other->id}")->assertNoContent();

    expect(sentTags())->toHaveCount(2)
        ->and(sentTags()[0])->toContain('post:bir')
        ->and(sentTags()[1])->toContain('post:ikki');
});

it('does not revalidate for drafts or pending-only edits', function () {
    frontend();
    $draft = Post::factory()->draft()->create();
    $published = Post::factory()->create();
    ready();

    $this->patchJson("/api/admin/posts/{$draft->id}", ['title' => 'Yangi nom', 'body' => 'boshqa'])->assertOk();
    $this->patchJson("/api/admin/posts/{$published->id}", ['title' => 'Kutilmoqda'])->assertOk();
    $this->deleteJson("/api/admin/posts/{$draft->id}")->assertNoContent();

    expect(sentTags())->toBeEmpty();
});

it('revalidates projects on create, update and delete', function () {
    frontend();
    ready();
    $created = $this->postJson('/api/admin/projects', ['name' => 'CRM'])->assertCreated()->json('id');
    $this->patchJson("/api/admin/projects/{$created}", ['name' => 'CRM 2'])->assertOk();
    $this->deleteJson("/api/admin/projects/{$created}")->assertNoContent();

    expect(sentTags())->toHaveCount(3)->and(sentTags()[0])->toBe(['projects']);
});

it('sends one request for a whole settings update', function () {
    frontend();
    ready();
    $this->putJson('/api/admin/settings', [
        'bio' => 'Salom',
        'open' => true,
        'github' => 'utkirbek',
        'now' => array_fill(0, 4, ['k' => 'a', 'v' => 'b']),
        'learn' => array_fill(0, 3, ['n' => 'a', 'p' => 10]),
        'telegram' => ['u' => 'kanal', 'n' => 'Nom', 'posts' => array_fill(0, 2, ['t' => 'x', 'd' => '2026-11-01'])],
    ])->assertOk();

    expect(sentTags())->toHaveCount(1)->and(sentTags()[0])->toBe(['settings']);
});

it('does nothing when the secret or url is missing', function () {
    config(['services.frontend.secret' => null]);
    frontend();

    $post = Post::factory()->draft()->create(['title' => 'Tayyor', 'body' => 'matn']);
    $this->postJson("/api/admin/posts/{$post->id}/publish")->assertOk();
    Project::factory()->create();
    Setting::create(['key' => 'x', 'value' => 1]);

    Http::assertNothingSent();
});

it('does not break the admin request or leak the secret when the frontend is down', function () {
    Log::spy();
    frontend(500);

    $post = Post::factory()->draft()->create(['title' => 'Tayyor', 'body' => 'matn']);

    $this->postJson("/api/admin/posts/{$post->id}/publish")->assertOk()->assertJsonPath('status', 'published');
    Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context = []) => ! str_contains(json_encode($context), 'sir-kalit'));
});
