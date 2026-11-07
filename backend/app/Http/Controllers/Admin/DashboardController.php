<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetDashboardData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PostBriefResource;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(GetDashboardData $getData): JsonResponse
    {
        $data = $getData();

        return response()->json([
            ...$data,
            'last_edited' => $data['last_edited'] ? (new PostBriefResource($data['last_edited']))->resolve() : null,
            'resume' => PostBriefResource::collection($data['resume'])->resolve(),
        ]);
    }
}
