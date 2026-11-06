<?php

namespace App\Http\Controllers;

use App\Actions\SearchSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchSite $search): JsonResponse
    {
        $query = mb_substr($request->string('q')->toString(), 0, 100);

        return response()->json($search($query));
    }
}
