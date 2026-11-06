<?php

namespace App\Http\Controllers;

use App\Actions\GetHomeData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GetHomeData $getHomeData): View
    {
        return view('pages.home', $getHomeData());
    }
}
