<?php

use App\Models\Project;
use App\Models\Setting;
use App\Services\Activity\ActivityProvider;
use App\Services\Activity\GithubClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('uses demo data when no username is set and sends no request', function () {
    Http::fake();

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeFalse()
        ->and($data['error'])->toBeFalse()
        ->and($data['days'])->toHaveCount(365);
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
        ->and($data['days'])->toHaveCount(365)
        ->and(array_sum(array_column($data['days'], 'c')))->toBe(5)
        ->and($data['info']['crm-system'])->toMatchArray(['lang' => 'PHP', 'stars' => 7])
        ->and($data['runs'])->toHaveCount(2)
        ->and($data['runs'][0]['status'])->toBe('running')
        ->and($data['runs'][1])->toMatchArray(['status' => 'failure', 'title' => 'fix: summa', 'dur' => 120, 'key' => 'crm-system', 'branch' => 'main', 'num' => 12]);

    Http::assertSent(fn ($request) => $request->url() === 'https://api.github.com/graphql' && $request->hasHeader('Authorization', 'Bearer maxfiy-token'));
});

it('reads the public profile calendar without a token', function () {
    config(['services.github.token' => null]);
    fakeGithub();
    connectGithub();

    $data = app(ActivityProvider::class)->get();

    expect($data['real'])->toBeTrue()
        ->and($data['days'])->toHaveCount(365)
        ->and(array_sum(array_column($data['days'], 'c')))->toBe(1310);
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'graphql'));
    Http::assertNotSent(fn ($request) => $request->hasHeader('Authorization'));
    Http::assertSent(fn ($request) => $request->url() === 'https://github.com/users/Utkirbek/contributions');
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/events/public'));
});

it('falls back to public events when the profile calendar fails', function () {
    config(['services.github.token' => null]);
    fakeGithub(['github.com/users/Utkirbek/contributions' => Http::response('xato', 500)]);
    connectGithub();

    $data = app(ActivityProvider::class)->get();

    expect(array_sum(array_column($data['days'], 'c')))->toBe(4);
});

it('falls back to public events when the calendar markup is unusable', function () {
    config(['services.github.token' => null]);
    fakeGithub(['github.com/users/Utkirbek/contributions' => Http::response('<html><body>Boshqa sahifa</body></html>')]);
    connectGithub();

    expect(array_sum(array_column(app(ActivityProvider::class)->get()['days'], 'c')))->toBe(4);
});

it('prefers the token calendar over the profile page', function () {
    config(['services.github.token' => 'maxfiy-token']);
    fakeGithub();
    connectGithub();

    app(ActivityProvider::class)->get();

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'github.com/users/Utkirbek/contributions'));
});

it('parses calendar markup with plural, singular, thousands and empty days', function () {
    $cells = '';
    $tips = '';

    foreach (range(1, 40) as $i) {
        $date = now()->subDays(40 - $i)->toDateString();
        $label = match (true) {
            $i === 1 => '1 contribution on a day.',
            $i === 2 => '1,234 contributions on a day.',
            $i === 3 => '7 contributions on a day.',
            default => 'No contributions on a day.',
        };
        $cells .= "<td data-date=\"{$date}\" id=\"d{$i}\" class=\"ContributionCalendar-day\"></td>";
        $tips .= "<tool-tip for=\"d{$i}\" class=\"sr-only\">{$label}</tool-tip>";
    }

    $counts = app(GithubClient::class)->parseCalendar($cells.$tips);
    $values = array_values($counts);

    expect($counts)->toHaveCount(40)
        ->and($values[0])->toBe(1)
        ->and($values[1])->toBe(1234)
        ->and($values[2])->toBe(7)
        ->and($values[3])->toBe(0);
});

it('rejects calendar markup with too few days', function () {
    expect(app(GithubClient::class)->parseCalendar('<td data-date="2026-01-01" id="a"></td><tool-tip for="a">3 contributions on x.</tool-tip>'))->toBeNull();
});

it('orders days oldest to newest and ends today', function () {
    fakeGithub();
    connectGithub();

    $days = app(ActivityProvider::class)->get()['days'];

    expect($days[364]['d'])->toBe(now()->toDateString())
        ->and($days[0]['d'])->toBe(now()->subDays(364)->toDateString());
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
