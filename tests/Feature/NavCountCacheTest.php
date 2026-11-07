<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NavCountCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_sonlar_keshlanadi_va_yozuv_oʻzgarganda_yangilanadi(): void
    {
        Post::factory()->create();
        $this->get('/')->assertOk();

        DB::enableQueryLog();
        $this->get('/fikrlar')->assertOk();
        $countQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'count(*)'))->count();
        DB::disableQueryLog();

        $this->assertSame(0, $countQueries);

        $post = Post::factory()->create();
        $this->assertMatchesRegularExpression('/<span>2<\/span><\/span>\s*<b>Fikrlar/', $this->get('/')->getContent());

        $post->delete();
        $this->assertMatchesRegularExpression('/<span>1<\/span><\/span>\s*<b>Fikrlar/', $this->get('/')->getContent());
    }
}
