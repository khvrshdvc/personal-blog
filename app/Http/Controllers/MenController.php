<?php

namespace App\Http\Controllers;

use App\Actions\GetMenData;
use Illuminate\View\View;

class MenController extends Controller
{
    public function __invoke(GetMenData $getMenData): View
    {
        return view('pages.men', $getMenData());
    }
}
