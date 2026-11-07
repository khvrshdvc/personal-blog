<?php

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Project;
use App\Services\SettingsService;
use Database\Seeders\PostSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\QueryException;

it('casts post category and status to enums', function () {
    $post = Post::factory()->dev()->draft()->create();

    expect($post->fresh()->category)->toBe(PostCategory::Dev)
        ->and($post->fresh()->status)->toBe(PostStatus::Draft);
});

it('scopes published posts', function () {
    Post::factory()->create();
    Post::factory()->draft()->create();

    expect(Post::published()->count())->toBe(1);
});

it('scopes posts by category', function () {
    Post::factory()->create();
    Post::factory()->dev()->create();

    expect(Post::inCategory(PostCategory::Dev)->count())->toBe(1);
});

it('orders posts newest first', function () {
    $old = Post::factory()->create(['published_at' => '2026-01-01']);
    $new = Post::factory()->create(['published_at' => '2026-06-01']);

    expect(Post::latestFirst()->pluck('id')->all())->toBe([$new->id, $old->id]);
});

it('keeps slugs unique', function () {
    Post::factory()->create(['slug' => 'bir']);

    Post::factory()->create(['slug' => 'bir']);
})->throws(QueryException::class);

it('stores pending changes as json', function () {
    $post = Post::factory()->create(['pending' => ['title' => 'Yangi']]);

    expect($post->fresh()->hasPending())->toBeTrue()
        ->and($post->fresh()->pending['title'])->toBe('Yangi');
});

it('reports no pending changes by default', function () {
    expect(Post::factory()->create()->hasPending())->toBeFalse();
});

it('computes words and read minutes', function () {
    $short = Post::factory()->make(['body' => 'bir ikki uch']);
    $long = Post::factory()->make(['body' => implode(' ', array_fill(0, 600, 'soz'))]);

    expect($short->words())->toBe(3)
        ->and($short->readMinutes())->toBe(1)
        ->and($long->readMinutes())->toBe(3);
});

it('resolves cover urls', function () {
    expect(Post::resolveCoverUrl(null))->toBeNull()
        ->and(Post::resolveCoverUrl('https://a.uz/c.jpg'))->toBe('https://a.uz/c.jpg')
        ->and(Post::resolveCoverUrl('/img/c.jpg'))->toBe('/img/c.jpg')
        ->and(Post::resolveCoverUrl('covers/x.jpg'))->toEndWith('/storage/covers/x.jpg')
        ->and(Post::resolveCoverUrl('//evil.uz/x.jpg'))->toEndWith('/storage///evil.uz/x.jpg');
});

it('casts project stack to an array', function () {
    $project = Project::factory()->create(['stack' => ['PHP', 'Redis']]);

    expect($project->fresh()->stack)->toBe(['PHP', 'Redis']);
});

it('stores settings through the service', function () {
    $settings = app(SettingsService::class);

    $settings->put('open', true);
    $settings->put('now', [['k' => 'a', 'v' => 'b']]);

    expect($settings->get('open'))->toBeTrue()
        ->and($settings->get('now')[0]['v'])->toBe('b')
        ->and($settings->get('yoq', 'bor'))->toBe('bor');
});

it('seeds sample data idempotently', function () {
    foreach ([1, 2] as $_) {
        $this->seed(PostSeeder::class);
        $this->seed(ProjectSeeder::class);
        $this->seed(SettingSeeder::class);
    }

    expect(Post::count())->toBe(13)
        ->and(Post::where('status', 'draft')->count())->toBe(2)
        ->and(Project::count())->toBe(5)
        ->and(app(SettingsService::class)->all())->toHaveCount(6);
});

it('seeds settings in the expected shape', function () {
    $this->seed(SettingSeeder::class);

    $settings = app(SettingsService::class);

    expect($settings->get('now'))->toHaveCount(4)
        ->and($settings->get('learn'))->toHaveCount(3)
        ->and($settings->get('telegram')['posts'])->toHaveCount(2);
});
