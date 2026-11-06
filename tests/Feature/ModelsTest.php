<?php

namespace Tests\Feature;

use App\Enums\PostCategory;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\Project;
use App\Services\SettingsService;
use Database\Seeders\PostSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_kategoriya_va_holat_enum_boladi(): void
    {
        $post = Post::factory()->dev()->draft()->create();

        $this->assertSame(PostCategory::Dev, $post->fresh()->category);
        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
    }

    public function test_published_scope_faqat_chop_etilganlarni_qaytaradi(): void
    {
        Post::factory()->create();
        Post::factory()->draft()->create();

        $this->assertCount(1, Post::published()->get());
    }

    public function test_slug_unikal(): void
    {
        Post::factory()->create(['slug' => 'bir']);

        $this->expectException(QueryException::class);

        Post::factory()->create(['slug' => 'bir']);
    }

    public function test_pending_json_sifatida_saqlanadi(): void
    {
        $post = Post::factory()->create(['pending' => ['title' => 'Yangi']]);

        $this->assertTrue($post->fresh()->hasPending());
        $this->assertSame('Yangi', $post->fresh()->pending['title']);
    }

    public function test_project_stack_massiv(): void
    {
        $project = Project::factory()->create(['stack' => ['PHP', 'Redis']]);

        $this->assertSame(['PHP', 'Redis'], $project->fresh()->stack);
    }

    public function test_sozlamalar_servisi_qiymatni_saqlaydi(): void
    {
        $settings = app(SettingsService::class);

        $settings->put('open', true);
        $settings->put('now', [['k' => 'a', 'v' => 'b']]);

        $this->assertTrue($settings->get('open'));
        $this->assertSame('b', $settings->get('now')[0]['v']);
        $this->assertSame('bor', $settings->get('yoq', 'bor'));
    }

    public function test_seeder_takroran_ishlaganda_dublikat_yaratmaydi(): void
    {
        foreach ([1, 2] as $_) {
            $this->seed(PostSeeder::class);
            $this->seed(ProjectSeeder::class);
            $this->seed(SettingSeeder::class);
        }

        $this->assertSame(13, Post::count());
        $this->assertSame(2, Post::where("status", "draft")->count());
        $this->assertSame(5, Project::count());
        $this->assertCount(6, app(SettingsService::class)->all());
    }
}
