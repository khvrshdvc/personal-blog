<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetDashboardData;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(GetDashboardData $getData): View
    {
        return view('admin.dashboard', $getData());
    }
}
