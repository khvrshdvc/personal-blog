<?php

use App\Models\Project;

beforeEach(fn () => $this->actingAs(admin()));

it('lists projects', function () {
    Project::factory()->count(2)->create();

    $this->getJson('/api/admin/projects')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonStructure([['id', 'name', 'repo', 'description', 'stack', 'demo_url']]);
});

it('creates a project and normalizes the fields', function () {
    $this->postJson('/api/admin/projects', [
        'name' => '  CRM  ',
        'repo' => 'https://github.com/utkirbek/crm.git',
        'description' => 'Mijozlar',
        'stack' => ['PHP', ' ', 'Laravel'],
        'demo_url' => 'crm.uz',
    ])
        ->assertCreated()
        ->assertJsonPath('name', 'CRM')
        ->assertJsonPath('repo', 'utkirbek/crm')
        ->assertJsonPath('stack', ['PHP', 'Laravel'])
        ->assertJsonPath('demo_url', 'https://crm.uz');
});

it('creates a project with only a name', function () {
    $this->postJson('/api/admin/projects', ['name' => 'Bo‘sh'])
        ->assertCreated()
        ->assertJsonPath('stack', [])
        ->assertJsonPath('repo', null);
});

it('validates the project payload', function () {
    $this->postJson('/api/admin/projects', [])->assertUnprocessable()->assertJsonValidationErrors('name');
    $this->postJson('/api/admin/projects', ['name' => 'A', 'description' => str_repeat('a', 221)])->assertUnprocessable()->assertJsonValidationErrors('description');
    $this->postJson('/api/admin/projects', ['name' => 'A', 'stack' => range(1, 9)])->assertUnprocessable()->assertJsonValidationErrors('stack');
    $this->postJson('/api/admin/projects', ['name' => 'A', 'stack' => 'PHP'])->assertUnprocessable();
});

it('drops an invalid demo url', function () {
    $this->postJson('/api/admin/projects', ['name' => 'A', 'demo_url' => 'javascript:alert(1)'])
        ->assertCreated()
        ->assertJsonPath('demo_url', null);
});

it('updates only the sent fields', function () {
    $project = Project::factory()->create(['name' => 'Eski', 'description' => 'Tavsif', 'stack' => ['PHP']]);

    $this->patchJson('/api/admin/projects/'.$project->id, ['name' => 'Yangi'])
        ->assertOk()
        ->assertJsonPath('name', 'Yangi')
        ->assertJsonPath('description', 'Tavsif')
        ->assertJsonPath('stack', ['PHP']);
});

it('refuses to blank the project name on update', function () {
    $project = Project::factory()->create();

    $this->patchJson('/api/admin/projects/'.$project->id, ['name' => ''])->assertUnprocessable()->assertJsonValidationErrors('name');
});

it('deletes a project with 204 and 404s afterwards', function () {
    $project = Project::factory()->create();

    $this->deleteJson('/api/admin/projects/'.$project->id)->assertNoContent();
    $this->patchJson('/api/admin/projects/'.$project->id, ['name' => 'X'])->assertNotFound();
});
