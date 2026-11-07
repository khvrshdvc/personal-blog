<?php

namespace App\Http\Controllers;

use App\Actions\Site\GetPost;
use App\Actions\Site\ListPosts;
use App\Actions\Site\ListTags;
use App\Http\Requests\ListPostsRequest;
use App\Http\Requests\ListTagsRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostDetailResource;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(ListPostsRequest $request, ListPosts $listPosts): PostCollection
    {
        return new PostCollection($listPosts($request->validated()));
    }

    public function show(string $slug, GetPost $getPost): PostDetailResource
    {
        $data = $getPost($slug);

        abort_if($data === null, 404, 'Yozuv topilmadi.');

        return new PostDetailResource($data);
    }

    public function tags(ListTagsRequest $request, ListTags $listTags): JsonResponse
    {
        return response()->json($listTags($request->validated('category')));
    }
}
