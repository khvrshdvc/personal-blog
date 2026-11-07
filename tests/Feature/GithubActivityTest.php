<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Setting;
use App\Services\Activity\ActivityProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GithubActivityTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGithub(array $override = []): void
    {
        $today = now('UTC')->toDateString();

        Http::fake($override + [
            'api.github.com/users/utkirbek' => Http::response(['login' => 'Utkirbek']),
            'api.github.com/users/Utkirbek/repos*' => Http::response([
                ['name' => 'crm-system', 'language' => 'PHP', 'stargazers_count' => 7, 'pushed_at' => now()->subHours(3)->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system'],
            ]),
            'api.github.com/repos/Utkirbek/crm-system/actions/runs*' => Http::response(['workflow_runs' => [
                ['name' => 'Tests', 'display_title' => "fix: summa\nqo'shimcha", 'head_branch' => 'main', 'run_number' => 12, 'status' => 'completed', 'conclusion' => 'failure', 'created_at' => now()->subMinutes(30)->toIso8601String(), 'run_started_at' => now()->subMinutes(30)->toIso8601String(), 'updated_at' => now()->subMinutes(28)->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system/actions/runs/1'],
                ['name' => 'Deploy', 'display_title' => 'feat: yangi', 'head_branch' => 'main', 'run_number' => 13, 'status' => 'in_progress', 'conclusion' => null, 'created_at' => now()->subMinutes(5)->toIso8601String(), 'updated_at' => now()->toIso8601String(), 'html_url' => 'https://github.com/Utkirbek/crm-system/actions/runs/2'],
            ]]),
            'api.github.com/graphql' => Http::response(['data' => ['user' => ['contributionsCollection' => ['contributionCalendar' => ['weeks' => [
                ['contributionDays' => [['date' => $today, 'contributionCount' => 5]]],
            ]]]]]]),
            'api.github.com/users/Utkirbek/events/public*' => Http::response([
                ['type' => 'PushEvent', 'created_at' => now()->toIso8601String(), 'payload' => ['size' => 3]],
                ['type' => 'IssuesEvent', 'created_at' => now()->toIso8601String(), 'payload' => []],
            ]),
        ]);
    }

    private function connect(): void
    {
        Setting::create(['key' => 'github', 'value' => 'utkirbek']);
        Project::factory()->create(['repo' => 'crm-system']);
        Project::factory()->create(['repo' => null]);
    }

    public function test_username_boʻlmasa_demo_ishlatiladi(): void
    {
        Http::fake();

        $data = app(ActivityProvider::class)->get();

        $this->assertFalse($data['real']);
        $this->assertFalse($data['error']);
        Http::assertNothingSent();
    }

    public function test_token_bilan_yillik_faollik_va_runlar_olinadi(): void
    {
        config(['services.github.token' => 'maxfiy-token']);
        $this->fakeGithub();
        $this->connect();

        $data = app(ActivityProvider::class)->get();

        $this->assertTrue($data['real']);
        $this->assertSame('Utkirbek', $data['login']);
        $this->assertSame('soʻnggi yil', $data['range']);
        $this->assertSame(5, collect($data['days'])->filter()->sum('c'));
        $this->assertSame('PHP', $data['info']['crm-system']['lang']);
        $this->assertSame(7, $data['info']['crm-system']['stars']);
        $this->assertCount(2, $data['runs']);
        $this->assertSame('running', $data['runs'][0]['status']);
        $this->assertSame('failure', $data['runs'][1]['status']);
        $this->assertSame('fix: summa', $data['runs'][1]['title']);
        $this->assertSame(120, $data['runs'][1]['dur']);
        $this->assertSame('crm-system', $data['runs'][1]['key']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.github.com/graphql' && $request->hasHeader('Authorization', 'Bearer maxfiy-token'));
    }

    public function test_tokensiz_ommaviy_hodisalar_ishlatiladi(): void
    {
        config(['services.github.token' => null]);
        $this->fakeGithub();
        $this->connect();

        $data = app(ActivityProvider::class)->get();

        $this->assertSame('soʻnggi 90 kun', $data['range']);
        $this->assertSame(4, collect($data['days'])->filter()->sum('c'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'graphql'));
        Http::assertNotSent(fn ($request) => $request->hasHeader('Authorization'));
    }

    public function test_natija_keshlanadi(): void
    {
        $this->fakeGithub();
        $this->connect();

        app(ActivityProvider::class)->get();
        $count = count(Http::recorded());
        app(ActivityProvider::class)->get();

        $this->assertSame($count, count(Http::recorded()));
    }

    public function test_loyihalar_oʻzgarsa_kesh_yangilanadi(): void
    {
        $this->fakeGithub();
        $this->connect();

        app(ActivityProvider::class)->get();
        $count = count(Http::recorded());
        Project::factory()->create(['repo' => 'boshqa-repo']);
        app(ActivityProvider::class)->get();

        $this->assertGreaterThan($count, count(Http::recorded()));
    }

    public function test_github_xato_bersa_demo_va_xato_belgisi(): void
    {
        Http::fake(['api.github.com/*' => Http::response(['message' => 'Not Found'], 404)]);
        $this->connect();

        $data = app(ActivityProvider::class)->get();

        $this->assertFalse($data['real']);
        $this->assertTrue($data['error']);
    }

    public function test_github_keyin_javob_bermasa_oldingi_natija_saqlanadi(): void
    {
        $this->fakeGithub();
        $this->connect();

        $this->assertTrue(app(ActivityProvider::class)->get()['real']);

        $this->travel(11)->minutes();
        Http::fake(['api.github.com/*' => Http::response([], 500)]);

        $data = app(ActivityProvider::class)->get();

        $this->assertTrue($data['real']);
        $this->assertFalse($data['error']);
        $this->assertSame('PHP', $data['info']['crm-system']['lang']);
    }

    public function test_xatodan_keyin_qayta_soʻrov_bir_daqiqa_toxtatiladi(): void
    {
        Http::fake(['api.github.com/*' => Http::response([], 500)]);
        $this->connect();

        app(ActivityProvider::class)->get();
        $count = count(Http::recorded());
        app(ActivityProvider::class)->get();

        $this->assertSame($count, count(Http::recorded()));
    }

    public function test_dasturlash_faollik_sahifasi_xatoni_aytadi(): void
    {
        Http::fake(['api.github.com/*' => Http::response([], 500)]);
        $this->connect();

        $this->get('/faollik')->assertOk()->assertSee('GitHub maʼlumotini yuklab boʻlmadi');
    }

    public function test_loyihalar_sahifasi_haqiqiy_havola_va_til_bilan(): void
    {
        $this->fakeGithub();
        Setting::create(['key' => 'github', 'value' => 'utkirbek']);
        Project::factory()->create(['name' => 'CRM tizimi', 'repo' => 'crm-system']);

        $this->get('/loyihalar')
            ->assertOk()
            ->assertSee('href="https://github.com/Utkirbek/crm-system"', false)
            ->assertSee('PHP')
            ->assertSee('CI ishlamoqda');
    }

    public function test_token_sahifada_va_logda_chiqmaydi(): void
    {
        config(['services.github.token' => 'maxfiy-token-12345']);
        Log::spy();
        Http::fake(['api.github.com/*' => Http::response([], 500)]);
        $this->connect();

        $html = $this->get('/faollik')->getContent().$this->get('/')->getContent();

        $this->assertStringNotContainsString('maxfiy-token-12345', $html);
        Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context = []) => ! str_contains(json_encode($context), 'maxfiy-token-12345'));
    }
}
