<?php

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => $this->actingAs(admin()));

const ADMIN_POST_KEYS = ['id', 'category', 'tag', 'title', 'slug', 'status', 'excerpt', 'cover', 'body', 'published_at', 'updated_at', 'pending', 'words'];

it('lists all posts including drafts in the admin shape', function () {
    Post::factory()->create();
    Post::factory()->draft()->create();

    $this->getJson('/api/admin/posts')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonStructure([ADMIN_POST_KEYS]);
});

it('filters admin posts by category and query', function () {
    Post::factory()->create(['title' => 'Redis haqida']);
    Post::factory()->dev()->create(['title' => 'Boshqa', 'body' => 'kesh bor']);

    expect($this->getJson('/api/admin/posts?category=dev')->json())->toHaveCount(1)
        ->and($this->getJson('/api/admin/posts?q=redis')->json())->toHaveCount(1)
        ->and($this->getJson('/api/admin/posts?q=kesh')->json())->toHaveCount(1);

    $this->getJson('/api/admin/posts?category=xato')->assertUnprocessable();
});

it('creates a draft with 201', function () {
    $this->postJson('/api/admin/posts', ['category' => 'dev', 'title' => 'Yangi yozuv', 'body' => 'matn'])
        ->assertCreated()
        ->assertJsonStructure(ADMIN_POST_KEYS)
        ->assertJsonPath('status', 'draft')
        ->assertJsonPath('category', 'dev')
        ->assertJsonPath('tag', 'Laravel')
        ->assertJsonPath('slug', 'yangi-yozuv')
        ->assertJsonPath('pending', null);
});

it('creates an empty draft and keeps slugs unique', function () {
    $this->postJson('/api/admin/posts', ['category' => 'fikr'])->assertCreated()->assertJsonPath('slug', 'qoralama');
    $this->postJson('/api/admin/posts', ['category' => 'fikr'])->assertCreated()->assertJsonPath('slug', 'qoralama-2');
});

it('validates post creation', function () {
    $this->postJson('/api/admin/posts', [])->assertUnprocessable()->assertJsonValidationErrors('category');
    $this->postJson('/api/admin/posts', ['category' => 'xato'])->assertUnprocessable();
    $this->postJson('/api/admin/posts', ['category' => 'fikr', 'title' => str_repeat('a', 256)])->assertUnprocessable();
});

it('shows a post and 404s for unknown ids', function () {
    $post = Post::factory()->draft()->create();

    $this->getJson('/api/admin/posts/'.$post->id)->assertOk()->assertJsonPath('id', $post->id);
    $this->getJson('/api/admin/posts/999999')->assertNotFound();
});

it('autosaves a draft directly with partial fields', function () {
    $post = Post::factory()->draft()->create(['title' => 'Eski', 'body' => 'eski matn']);

    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Yangi sarlavha'])
        ->assertOk()
        ->assertJsonPath('title', 'Yangi sarlavha')
        ->assertJsonPath('body', 'eski matn')
        ->assertJsonPath('pending', null);
});

it('lets a draft take a requested slug and makes it unique on conflict', function () {
    Post::factory()->create(['slug' => 'band']);
    $post = Post::factory()->draft()->create();

    $this->patchJson('/api/admin/posts/'.$post->id, ['slug' => 'Mening Havolam!'])->assertJsonPath('slug', 'mening-havolam');
    $this->patchJson('/api/admin/posts/'.$post->id, ['slug' => 'band'])->assertJsonPath('slug', 'band-2');
});

it('replaces an unknown tag with the first tag of the category', function () {
    $post = Post::factory()->draft()->create();

    $this->patchJson('/api/admin/posts/'.$post->id, ['category' => 'dev', 'tag' => 'Yoq'])
        ->assertJsonPath('tag', 'Laravel');
});

it('writes edits to a published post into pending, not into the live fields', function () {
    $post = Post::factory()->create(['title' => 'Asl sarlavha', 'body' => 'asl matn']);

    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Yangi sarlavha'])
        ->assertOk()
        ->assertJsonPath('title', 'Asl sarlavha')
        ->assertJsonPath('pending.title', 'Yangi sarlavha')
        ->assertJsonPath('pending.body', 'asl matn');

    $this->getJson('/api/posts/'.$post->slug)->assertJsonPath('title', 'Asl sarlavha');
});

it('clears pending when an edit reverts to the live content', function () {
    $post = Post::factory()->create(['title' => 'Asl']);

    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Boshqa'])->assertJsonPath('pending.title', 'Boshqa');
    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Asl'])->assertJsonPath('pending', null);
});

it('validates autosave fields', function () {
    $post = Post::factory()->draft()->create();

    $this->patchJson('/api/admin/posts/'.$post->id, ['published_at' => '07.11.2026'])->assertUnprocessable()->assertJsonValidationErrors('published_at');
    $this->patchJson('/api/admin/posts/'.$post->id, ['cover' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors('cover');
    $this->patchJson('/api/admin/posts/'.$post->id, ['category' => 'xato'])->assertUnprocessable();
});

it('accepts uploaded paths and https urls as cover', function () {
    $post = Post::factory()->draft()->create();

    $this->patchJson('/api/admin/posts/'.$post->id, ['cover' => 'covers/abc.jpg'])->assertOk()->assertJsonPath('cover', 'covers/abc.jpg');
    $this->patchJson('/api/admin/posts/'.$post->id, ['cover' => 'https://a.uz/c.png'])->assertOk();
});

it('publishes a complete draft', function () {
    $post = Post::factory()->draft()->create(['title' => 'Tayyor', 'body' => 'Matn **bor**', 'excerpt' => null, 'slug' => 'tayyor']);

    $this->postJson('/api/admin/posts/'.$post->id.'/publish')
        ->assertOk()
        ->assertJsonPath('status', 'published')
        ->assertJsonPath('excerpt', 'Matn bor');

    $this->getJson('/api/posts/tayyor')->assertOk();
});

it('refuses to publish an empty title or body', function () {
    $noTitle = Post::factory()->draft()->create(['title' => '', 'body' => 'matn']);
    $noBody = Post::factory()->draft()->create(['title' => 'Bor', 'body' => '  ']);

    $this->postJson('/api/admin/posts/'.$noTitle->id.'/publish')->assertUnprocessable()->assertJsonValidationErrors('title');
    $this->postJson('/api/admin/posts/'.$noBody->id.'/publish')->assertUnprocessable()->assertJsonValidationErrors('body');
});

it('refuses to apply a pending slug that another post already uses', function () {
    Post::factory()->create(['slug' => 'band']);
    $post = Post::factory()->create(['slug' => 'erkin']);
    $this->patchJson('/api/admin/posts/'.$post->id, ['slug' => 'band'])->assertJsonPath('pending.slug', 'band');

    $this->postJson('/api/admin/posts/'.$post->id.'/apply')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('slug');

    expect($post->fresh()->slug)->toBe('erkin');
});

it('returns 409 when publishing an already published post', function () {
    $post = Post::factory()->create();

    $this->postJson('/api/admin/posts/'.$post->id.'/publish')->assertStatus(409);
});

it('applies pending changes to the live post', function () {
    $post = Post::factory()->create(['title' => 'Asl', 'slug' => 'asl']);
    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Yangilangan', 'slug' => 'yangi']);

    $this->postJson('/api/admin/posts/'.$post->id.'/apply')
        ->assertOk()
        ->assertJsonPath('title', 'Yangilangan')
        ->assertJsonPath('slug', 'yangi')
        ->assertJsonPath('pending', null);

    $this->getJson('/api/posts/yangi')->assertOk()->assertJsonPath('title', 'Yangilangan');
});

it('returns 409 when applying without pending changes or on a draft', function () {
    $published = Post::factory()->create();
    $draft = Post::factory()->draft()->create();

    $this->postJson('/api/admin/posts/'.$published->id.'/apply')->assertStatus(409);
    $this->postJson('/api/admin/posts/'.$draft->id.'/apply')->assertStatus(409);
});

it('refuses to apply a pending change that blanks the title', function () {
    $post = Post::factory()->create();
    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => '']);

    $this->postJson('/api/admin/posts/'.$post->id.'/apply')->assertUnprocessable()->assertJsonValidationErrors('title');
});

it('discards pending changes', function () {
    $post = Post::factory()->create(['title' => 'Asl']);
    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Boshqa']);

    $this->deleteJson('/api/admin/posts/'.$post->id.'/pending')
        ->assertOk()
        ->assertJsonPath('title', 'Asl')
        ->assertJsonPath('pending', null);
});

it('unpublishes a post and carries pending changes into the draft', function () {
    $post = Post::factory()->create(['title' => 'Asl', 'slug' => 'asl']);
    $this->patchJson('/api/admin/posts/'.$post->id, ['title' => 'Tahrirlangan']);

    $this->postJson('/api/admin/posts/'.$post->id.'/unpublish')
        ->assertOk()
        ->assertJsonPath('status', 'draft')
        ->assertJsonPath('title', 'Tahrirlangan')
        ->assertJsonPath('pending', null);

    $this->getJson('/api/posts/asl')->assertNotFound();
});

it('deletes a post with 204', function () {
    $post = Post::factory()->create();

    $this->deleteJson('/api/admin/posts/'.$post->id)->assertNoContent();

    expect(Post::count())->toBe(0);
});

it('renders a markdown preview and escapes html', function () {
    $this->postJson('/api/admin/preview', ['body' => "## Sarlavha\n\n<script>x</script>"])
        ->assertOk()
        ->assertJsonPath('html', fn ($html) => str_contains($html, '<h2 id="sarlavha">') && ! str_contains($html, '<script'));
});

it('uploads a cover image', function () {
    Storage::fake('public');

    $response = $this->post('/api/admin/uploads', ['file' => UploadedFile::fake()->image('c.jpg', 600, 300)], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonStructure(['url', 'path']);

    Storage::disk('public')->assertExists($response->json('path'));
    expect($response->json('path'))->toStartWith('covers/');
});

it('rejects non-image and oversized uploads', function () {
    Storage::fake('public');

    $this->post('/api/admin/uploads', ['file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    $this->post('/api/admin/uploads', ['file' => UploadedFile::fake()->image('big.jpg')->size(6000)], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    $this->post('/api/admin/uploads', [], ['Accept' => 'application/json'])
        ->assertUnprocessable()->assertJsonValidationErrors('file');
    $this->post('/api/admin/uploads', ['file' => UploadedFile::fake()->create('x.svg', 5, 'image/svg+xml')], ['Accept' => 'application/json'])
        ->assertUnprocessable();
});
