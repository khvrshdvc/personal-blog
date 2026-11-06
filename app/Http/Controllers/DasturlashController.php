<?php

namespace App\Http\Controllers;

use App\Actions\GetDevPage;
use Illuminate\View\View;

class DasturlashController extends Controller
{
    public function yozuvlar(GetDevPage $getPage): View
    {
        return view('pages.dasturlash', $getPage('yozuvlar'));
    }

    public function loyihalar(GetDevPage $getPage): View
    {
        return view('pages.dasturlash', $getPage('loyihalar'));
    }

    public function faollik(GetDevPage $getPage): View
    {
        return view('pages.dasturlash', $getPage('faollik'));
    }
}
