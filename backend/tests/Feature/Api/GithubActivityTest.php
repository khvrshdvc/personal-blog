<?php

use App\Models\Project;
use App\Models\Setting;
use App\Services\Activity\ActivityProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('uses demo data when no username is set and sends no request', function () {
    Http::fake();

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeFalse()
        ->and($data['error'])->toBeFalse()
        ->and($data['days'])->toHaveCount(60);
    Http::assertNothingSent();
});

it('rejects malformed usernames without any request', function () {
    Http::fake();

    foreach (['../etc/passwd', 'a b', '-bad', 'x?y=1', str_repeat('a', 50)] as $bad) {
        Setting::updateOrCreate(['key' => 'github'], ['value' => $bad]);

        expect(app(ActivityProvider::class)->get()['real'])->toBeFalse();
    }

    Http::assertNothingSent();
});

it('reads the yearly calendar and runs with a token', function () {
    config(['services.github.token' => 'maxfiy-token']);
    fakeGithub();
    connectGithub();

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeTrue()
        ->and($data['login'])->toBe('Utkirbek')
        ->and($data['days'])->toHaveCount(60)
        ->and(array_sum(array_column($data['days'], 'c')))->toBe(5)
        ->and($data['info']['crm-system'])->toMatchArray(['lang' => 'PHP', 'stars' => 7])
        ->and($data['runs'])->toHaveCount(2)
        ->and($data['runs'][0]['status'])->toBe('running')
        ->and($data['runs'][1])->toMatchArray(['status' => 'failure', 'title' => 'fix: summa', 'dur' => 120, 'key' => 'crm-system', 'branch' => 'main', 'num' => 12]);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.github.com/graphql' && $request->hasHeader('Authorization', 'Bearer maxfiy-token'));
});

it('falls back to public events without a token', function () {
    config(['services.github.token' => null]);
    fakeGithub();
    connectGithub();

    $data = app(ActivityProvider::class)->get();

    expect(array_sum(array_column($data['days'], 'c')))->toBe(4);
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'graphql'));
    Http::assertNotSent(fn ($request) => $request->hasHeader('Authorization'));
});

it('orders days oldest to newest and ends today', function () {
    fakeGithub();
    connectGithub();

    $days = app(ActivityProvider::class)->get()['days'];

    expect($days[59]['d'])->toBe(now()->toDateString())
        ->and($days[0]['d'])->toBe(now()->subDays(59)->toDateString());
});

it('caches the result', function () {
    fakeGithub();
    connectGithub();

    app(ActivityProvider::class)->get();
    $count = count(Http::recorded());
    app(ActivityProvider::class)->get();

    expect(Http::recorded())->toHaveCount($count);
});

it('refreshes the cache when the project repos change', function () {
    fakeGithub();
    connectGithub();

    app(ActivityProvider::class)->get();
    $count = count(Http::recorded());
    Project::factory()->create(['repo' => 'boshqa-repo']);
    app(ActivityProvider::class)->get();

    expect(count(Http::recorded()))->toBeGreaterThan($count);
});

it('falls back to demo data with an error flag when GitHub fails', function () {
    Http::fake(['api.github.com/*' => Http::response(['message' => 'Not Found'], 404)]);
    connectGithub();

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeFalse()->and($data['error'])->toBeTrue();
});

it('keeps the previous result when GitHub stops answering', function () {
    fakeGithub();
    connectGithub();

    expect(app(ActivityProvider::class)->get()['real'])->toBeTrue();

    $this->travel(11)->minutes();
    Http::fake(['api.github.com/*' => Http::response([], 500)]);

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeTrue()
        ->and($data['error'])->toBeFalse()
        ->and($data['info']['crm-system']['lang'])->toBe('PHP');
});

it('pauses retries for a minute after a failure', function () {
    Http::fake(['api.github.com/*' => Http::response([], 500)]);
    connectGithub();

    app(ActivityProvider::class)->get();
    $count = count(Http::recorded());
    app(ActivityProvider::class)->get();

    expect(Http::recorded())->toHaveCount($count);
});

it('never leaks the token in responses or logs', function () {
    config(['services.github.token' => 'maxfiy-token-12345']);
    Log::spy();
    Http::fake(['api.github.com/*' => Http::response([], 500)]);
    connectGithub();

    $body = $this->getJson('/api/activity')->getContent().$this->getJson('/api/runs')->getContent();

    expect($body)->not->toContain('maxfiy-token-12345');
    Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context = []) => ! str_contains(json_encode($context), 'maxfiy-token-12345'));
});
