<?php

namespace Tests\Feature\Admin;

use App\Models\Project;

class ProjectAdminTest extends AdminTestCase
{
    public function test_sahifalar_ochiladi(): void
    {
        $project = Project::factory()->create(['name' => 'CRM tizimi']);

        $this->get('/admin/projects')->assertOk()->assertSee('CRM tizimi');
        $this->get('/admin/projects/new')->assertOk()->assertSee('Saytda shunday koʻrinadi');
        $this->get('/admin/projects/'.$project->id)->assertOk()->assertSee('Loyiha maʼlumotlari');
        $this->get('/admin/projects/999999')->assertNotFound();
    }

    public function test_yaratish_va_normalizatsiya(): void
    {
        $this->post('/admin/projects', [
            'name' => '  CRM tizimi ',
            'repo' => 'https://github.com/utkirbek/crm-system.git',
            'description' => 'Tavsif',
            'stack' => 'Laravel, MySQL , , Redis',
            'demo_url' => 'demo.uz',
        ])->assertRedirect()->assertSessionHas('toast');

        $project = Project::first();

        $this->assertSame('CRM tizimi', $project->name);
        $this->assertSame('utkirbek/crm-system', $project->repo);
        $this->assertSame(['Laravel', 'MySQL', 'Redis'], $project->stack);
        $this->assertSame('https://demo.uz', $project->demo_url);
    }

    public function test_stack_8_tadan_oshmaydi(): void
    {
        $this->post('/admin/projects', ['name' => 'X', 'stack' => implode(',', range(1, 12))]);

        $this->assertCount(8, Project::first()->stack);
    }

    public function test_nom_majburiy(): void
    {
        $this->post('/admin/projects', ['name' => ''])->assertSessionHasErrors(['name' => 'Loyiha nomini yozing']);

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_notoʻgʻri_demo_havola_saqlanmaydi(): void
    {
        $this->post('/admin/projects', ['name' => 'X', 'demo_url' => 'javascript:alert(1)']);

        $this->assertNull(Project::first()->demo_url);
    }

    public function test_yangilash(): void
    {
        $project = Project::factory()->create(['name' => 'Eski']);

        $this->put('/admin/projects/'.$project->id, ['name' => 'Yangi', 'repo' => 'a/b'])->assertRedirect(route('admin.projects.edit', $project));

        $this->assertSame('Yangi', $project->fresh()->name);
        $this->assertSame('a/b', $project->fresh()->repo);
    }

    public function test_oʻchirish(): void
    {
        $project = Project::factory()->create();

        $this->deleteJson('/admin/projects/'.$project->id)->assertOk()->assertJsonPath('redirect', route('admin.projects.index'));

        $this->assertModelMissing($project);
    }

    public function test_saytda_koʻrinadi(): void
    {
        $this->post('/admin/projects', ['name' => 'Yangi loyiha nomi']);

        auth()->logout();
        $this->get('/loyihalar')->assertSee('Yangi loyiha nomi');
    }
}
