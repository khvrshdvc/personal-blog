<?php

namespace App\Http\Controllers;

use App\Actions\GetPostPage;
use App\Enums\PostCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    public function show(string $slug, GetPostPage $getPage): View
    {
        $data = $getPage($slug, Auth::check());

        abort_if($data === null, 404);

        view()->share('navSection', $data['post']->category === PostCategory::Dev ? 'dasturlash' : 'fikrlar');

        return view('pages.post', $data);
    }
}
