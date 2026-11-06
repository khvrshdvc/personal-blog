<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_bosh_sorov_songgi_yozuvlar_va_sahifalarni_qaytaradi(): void
    {
        Post::factory()->count(7)->create();

        $groups = collect($this->getJson('/qidiruv')->assertOk()->json())->pluck('g');

        $this->assertSame(5, $groups->filter(fn ($g) => $g === 'Soʻnggi yozuvlar')->count());
        $this->assertContains('Sahifalar', $groups);
    }

    public function test_yozuv_sarlavha_va_matn_boyicha_topiladi(): void
    {
        Post::factory()->create(['title' => 'Redis haqida', 'body' => 'oddiy matn']);
        Post::factory()->create(['title' => 'Boshqa', 'body' => 'ichida kesh soʻzi bor']);
        Post::factory()->draft()->create(['title' => 'Redis qoralama']);

        $titles = collect($this->getJson('/qidiruv?q=redis')->json())->pluck('t');
        $this->assertTrue($titles->contains('Redis haqida'));
        $this->assertFalse($titles->contains('Redis qoralama'));

        $titles = collect($this->getJson('/qidiruv?q=kesh')->json())->pluck('t');
        $this->assertTrue($titles->contains('Boshqa'));
    }

    public function test_loyiha_va_sahifa_topiladi(): void
    {
        Project::factory()->create(['name' => 'CRM tizimi', 'stack' => ['Laravel']]);

        $this->assertTrue(collect($this->getJson('/qidiruv?q=crm')->json())->contains('g', 'Loyihalar'));
        $this->assertTrue(collect($this->getJson('/qidiruv?q=men')->json())->contains('t', 'Men haqimda'));
    }

    public function test_hech_narsa_topilmasa_bosh_royxat(): void
    {
        $this->getJson('/qidiruv?q=yoqqiydirsoz')->assertOk()->assertExactJson([]);
    }

    public function test_like_maxsus_belgilari_ekranlanadi(): void
    {
        Post::factory()->create(['title' => 'Oddiy sarlavha']);

        $this->getJson('/qidiruv?q=%25')->assertOk()->assertExactJson([]);
    }
}
