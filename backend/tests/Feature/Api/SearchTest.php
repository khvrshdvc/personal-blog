<?php

use App\Models\Post;
use App\Models\Project;

it('returns the latest five posts for an empty query', function () {
    Post::factory()->count(7)->create();
    Post::factory()->draft()->create();

    $items = $this->getJson('/api/search')->assertOk()->json();

    expect($items)->toHaveCount(5)
        ->and(collect($items)->pluck('type')->unique()->all())->toBe(['post']);
});

it('finds posts by title and body but not drafts', function () {
    Post::factory()->create(['title' => 'Redis haqida', 'tag' => 'Laravel', 'body' => 'oddiy']);
    Post::factory()->create(['title' => 'Boshqa', 'body' => 'ichida kesh soʻzi']);
    Post::factory()->draft()->create(['title' => 'Redis qoralama']);

    $titles = collect($this->getJson('/api/search?q=redis')->json())->pluck('title');
    expect($titles->all())->toBe(['Redis haqida']);

    expect(collect($this->getJson('/api/search?q=kesh')->json())->pluck('title')->all())->toBe(['Boshqa']);
});

it('returns documented item shape for posts and projects', function () {
    $post = Post::factory()->create(['title' => 'CRM yozuvi', 'tag' => 'Laravel']);
    Project::factory()->create(['name' => 'CRM tizimi', 'description' => 'Mijozlar']);

    $items = $this->getJson('/api/search?q=crm')->json();

    expect($items)->toContain(
        ['type' => 'post', 'category' => 'fikr', 'title' => 'CRM yozuvi', 'subtitle' => 'Laravel', 'path' => '/post/'.$post->slug],
        ['type' => 'project', 'category' => null, 'title' => 'CRM tizimi', 'subtitle' => 'Mijozlar', 'path' => '/loyihalar'],
    );
});

it('caps results at eight', function () {
    Post::factory()->count(12)->create(['title' => 'Umumiy sarlavha']);

    expect($this->getJson('/api/search?q=umumiy')->json())->toHaveCount(8);
});

it('returns an empty list when nothing matches', function () {
    $this->getJson('/api/search?q=yoqqiydirsoz')->assertOk()->assertExactJson([]);
});

it('rejects an overly long query', function () {
    $this->getJson('/api/search?q='.str_repeat('a', 101))->assertUnprocessable();
});
