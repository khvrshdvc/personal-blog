<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ProjectResource::collection(Project::orderBy('id')->get())->resolve());
    }

    public function store(SaveProjectRequest $request, SaveProject $save): JsonResponse
    {
        return (new ProjectResource($save(null, $request->validated())))->response()->setStatusCode(201);
    }

    public function update(SaveProjectRequest $request, Project $project, SaveProject $save): ProjectResource
    {
        return new ProjectResource($save($project, $request->validated()));
    }

    public function destroy(Project $project): Response
    {
        $project->delete();

        return response()->noContent();
    }
}
