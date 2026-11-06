<?php

namespace App\Http\Controllers;

use App\Actions\GetFikrlarPage;
use App\Actions\SearchFikrlar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FikrlarController extends Controller
{
    public function index(GetFikrlarPage $getPage): View
    {
        return view('pages.fikrlar', $getPage());
    }

    public function search(Request $request, SearchFikrlar $search): JsonResponse
    {
        return response()->json($search(mb_substr($request->string('q')->toString(), 0, 100)));
    }
}
