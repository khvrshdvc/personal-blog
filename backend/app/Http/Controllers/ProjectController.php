<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::query()->orderBy('id')->get();

        return response()->json(ProjectResource::collection($projects)->resolve());
    }
}
