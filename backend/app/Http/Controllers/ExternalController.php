<?php

namespace App\Http\Controllers;

use App\Actions\Site\GetActivity;
use App\Actions\Site\GetRepoInfo;
use App\Actions\Site\GetRuns;
use App\Actions\Site\GetTelegram;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ExternalController extends Controller
{
    public function activity(GetActivity $getActivity): JsonResponse
    {
        return response()->json($getActivity());
    }

    public function runs(GetRuns $getRuns): JsonResponse
    {
        return response()->json($getRuns());
    }

    public function projectRuns(Project $project, GetRuns $getRuns): JsonResponse
    {
        return response()->json($getRuns($project));
    }

    public function projectGithub(Project $project, GetRepoInfo $getInfo): JsonResponse
    {
        return response()->json($getInfo($project));
    }

    public function telegram(GetTelegram $getTelegram): JsonResponse
    {
        return response()->json($getTelegram());
    }
}
