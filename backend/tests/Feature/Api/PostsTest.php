<?php

use App\Models\Post;

it('lists only published posts, newest first, with the documented shape', function () {
    $old = Post::factory()->create(['published_at' => '2026-01-01', 'cover' => 'https://a.uz/c.jpg']);
    $new = Post::factory()->create(['published_at' => '2026-06-01']);
    Post::factory()->draft()->create();

    $response = $this->getJson('/api/posts')->assertOk();

    expect(array_column($response->json('data'), 'id'))->toBe([$new->id, $old->id]);

    $response->assertJsonStructure([
        'data' => [['id', 'category', 'tag', 'title', 'slug', 'excerpt', 'cover', 'published_at', 'read_minutes']],
        'meta' => ['current_page', 'last_page', 'per_page', 'total'],
    ])->assertJsonMissingPath('links')
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('data.1.cover', 'https://a.uz/c.jpg')
        ->assertJsonPath('data.0.published_at', '2026-06-01');
});

it('does not leak body or pending in the list', function () {
    Post::factory()->create(['pending' => ['title' => 'Yashirin']]);

    $item = $this->getJson('/api/posts')->json('data.0');

    expect($item)->not->toHaveKeys(['body', 'pending', 'status']);
});

it('filters by category, tag and query', function () {
    Post::factory()->create(['title' => 'Redis haqida', 'tag' => 'Hayot']);
    Post::factory()->dev()->create(['title' => 'Boshqa', 'tag' => 'Laravel', 'body' => 'ichida kesh bor']);

    expect($this->getJson('/api/posts?category=dev')->json('meta.total'))->toBe(1)
        ->and($this->getJson('/api/posts?tag=Hayot')->json('data.0.title'))->toBe('Redis haqida')
        ->and($this->getJson('/api/posts?q=redis')->json('meta.total'))->toBe(1)
        ->and($this->getJson('/api/posts?q=kesh')->json('data.0.title'))->toBe('Boshqa');
});

it('treats like wildcards in the query literally', function () {
    Post::factory()->create(['title' => 'Oddiy', 'body' => 'matn']);

    expect($this->getJson('/api/posts?q=%25')->json('meta.total'))->toBe(0);
});

it('paginates with per_page', function () {
    Post::factory()->count(5)->create();

    $this->getJson('/api/posts?per_page=2&page=3')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 3)
        ->assertJsonPath('meta.last_page', 3)
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonCount(1, 'data');
});

it('validates list parameters', function () {
    $this->getJson('/api/posts?category=xato')->assertUnprocessable()->assertJsonValidationErrors('category');
    $this->getJson('/api/posts?per_page=500')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    $this->getJson('/api/posts?page=0')->assertUnprocessable()->assertJsonValidationErrors('page');
});

it('shows a full post with html, toc and neighbours', function () {
    $older = Post::factory()->create(['published_at' => '2026-01-01']);
    $post = Post::factory()->create([
        'published_at' => '2026-02-01',
        'body' => "## Birinchi\n\nMatn **qalin**\n\n### Ichki",
    ]);
    $newer = Post::factory()->create(['published_at' => '2026-03-01']);
    Post::factory()->dev()->create(['published_at' => '2026-02-15']);

    $this->getJson('/api/posts/'.$post->slug)
        ->assertOk()
        ->assertJsonPath('slug', $post->slug)
        ->assertJsonPath('toc.0', ['id' => 'birinchi', 'text' => 'Birinchi', 'level' => 2])
        ->assertJsonPath('prev.id', $older->id)
        ->assertJsonPath('next.id', $newer->id)
        ->assertJsonMissingPath('prev.body_html')
        ->assertJsonMissingPath('body')
        ->assertJsonMissingPath('pending')
        ->assertJson(fn ($json) => $json->where('body_html', fn ($html) => str_contains($html, '<strong>qalin</strong>'))->etc());
});

it('returns null neighbours at the edges', function () {
    $only = Post::factory()->create();

    $this->getJson('/api/posts/'.$only->slug)
        ->assertOk()
        ->assertJsonPath('prev', null)
        ->assertJsonPath('next', null);
});

it('sanitizes html in the post body', function () {
    $post = Post::factory()->create(['body' => '<script>alert(1)</script> [x](javascript:alert)']);

    $html = $this->getJson('/api/posts/'.$post->slug)->json('body_html');

    expect($html)->not->toContain('<script')->not->toContain('javascript:');
});

it('hides drafts and unknown slugs behind 404 json', function () {
    $draft = Post::factory()->draft()->create();

    $this->getJson('/api/posts/'.$draft->slug)->assertNotFound()->assertJsonStructure(['message']);
    $this->getJson('/api/posts/yoq-slug')->assertNotFound();
});

it('lists tags with counts', function () {
    Post::factory()->count(2)->create(['tag' => 'Hayot']);
    Post::factory()->dev()->create(['tag' => 'Laravel']);
    Post::factory()->draft()->create(['tag' => 'Qoralama']);
    Post::factory()->create(['tag' => null]);

    $this->getJson('/api/tags')
        ->assertOk()
        ->assertExactJson([['tag' => 'Hayot', 'count' => 2], ['tag' => 'Laravel', 'count' => 1]]);

    $this->getJson('/api/tags?category=dev')->assertExactJson([['tag' => 'Laravel', 'count' => 1]]);
    $this->getJson('/api/tags?category=xato')->assertUnprocessable();
});
