<?php

namespace App\Http\Controllers;

use App\Actions\Site\GetPublicSettings;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __invoke(GetPublicSettings $getSettings): JsonResponse
    {
        return response()->json($getSettings());
    }
}
