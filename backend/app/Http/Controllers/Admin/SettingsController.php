<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateSettings;
use App\Actions\Site\GetPublicSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function show(GetPublicSettings $getSettings): JsonResponse
    {
        return response()->json($getSettings());
    }

    public function update(UpdateSettingsRequest $request, UpdateSettings $update): JsonResponse
    {
        return response()->json($update($request->validated()));
    }
}
