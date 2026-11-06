<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_mehmon_hamma_admin_sahifalaridan_loginga_yoʻnaltiriladi(): void
    {
        $post = Post::factory()->create();
        $project = Project::factory()->create();

        foreach (['/admin', '/admin/posts', '/admin/posts/new', '/admin/posts/new/dev', '/admin/posts/'.$post->id, '/admin/projects', '/admin/projects/new', '/admin/projects/'.$project->id, '/admin/settings'] as $path) {
            $this->get($path)->assertRedirect(route('admin.login'));
        }
    }

    public function test_mehmon_json_api_dan_foydalana_olmaydi(): void
    {
        $post = Post::factory()->create();

        $this->postJson('/admin/posts', ['title' => 'x'])->assertUnauthorized();
        $this->putJson('/admin/posts/'.$post->id, ['title' => 'x'])->assertUnauthorized();
        $this->postJson('/admin/posts/'.$post->id.'/publish')->assertUnauthorized();
        $this->deleteJson('/admin/posts/'.$post->id)->assertUnauthorized();
        $this->putJson('/admin/settings', [])->assertUnauthorized();
        $this->postJson('/admin/posts/preview', ['body' => 'x'])->assertUnauthorized();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
