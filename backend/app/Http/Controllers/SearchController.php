<?php

namespace App\Http\Controllers;

use App\Actions\Site\SearchContent;
use App\Http\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request, SearchContent $search): JsonResponse
    {
        return response()->json($search((string) $request->validated('q')));
    }
}
