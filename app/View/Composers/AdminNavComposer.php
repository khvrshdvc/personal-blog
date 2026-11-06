<?php

namespace App\View\Composers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\View\View;

class AdminNavComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'postTotal' => Post::count(),
            'projectTotal' => Project::count(),
        ]);
    }
}
