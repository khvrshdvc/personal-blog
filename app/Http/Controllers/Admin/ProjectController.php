<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('admin.projects.index', ['projects' => Project::orderBy('id')->get(), 'active' => null]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['projects' => Project::orderBy('id')->get(), 'active' => null, 'project' => null]);
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', ['projects' => Project::orderBy('id')->get(), 'active' => $project->id, 'project' => $project]);
    }

    public function store(SaveProjectRequest $request, SaveProject $save): RedirectResponse
    {
        return $this->saved($save(null, $request->validated()));
    }

    public function update(SaveProjectRequest $request, Project $project, SaveProject $save): RedirectResponse
    {
        return $this->saved($save($project, $request->validated()));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(['redirect' => route('admin.projects.index'), 'message' => 'Loyiha oʻchirildi']);
    }

    private function saved(Project $project): RedirectResponse
    {
        return redirect()->route('admin.projects.edit', $project)->with('toast', [
            'text' => 'Loyiha saqlandi',
            'action' => ['label' => 'Saytda koʻrish', 'href' => route('loyihalar')],
        ]);
    }
}
