<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ApplyPendingChanges;
use App\Actions\Admin\CreatePost;
use App\Actions\Admin\DiscardPendingChanges;
use App\Actions\Admin\PublishPost;
use App\Actions\Admin\SavePost;
use App\Actions\Admin\UnpublishPost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePostRequest;
use App\Http\Requests\Admin\ListAdminPostsRequest;
use App\Http\Requests\Admin\PreviewRequest;
use App\Http\Requests\Admin\SavePostRequest;
use App\Http\Requests\Admin\UploadCoverRequest;
use App\Http\Resources\Admin\AdminPostResource;
use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function index(ListAdminPostsRequest $request): JsonResponse
    {
        $query = Post::query()->orderByDesc('updated_at')->orderByDesc('id');

        if ($request->filled('category')) {
            $query->where('category', $request->validated('category'));
        }

        if ($request->filled('q')) {
            $like = '%'.addcslashes($request->validated('q'), '%_\\').'%';
            $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('body', 'like', $like));
        }

        return response()->json(AdminPostResource::collection($query->get())->resolve());
    }

    public function store(CreatePostRequest $request, CreatePost $create): JsonResponse
    {
        return (new AdminPostResource($create($request->validated())))->response()->setStatusCode(201);
    }

    public function show(Post $post): AdminPostResource
    {
        return new AdminPostResource($post);
    }

    public function update(SavePostRequest $request, Post $post, SavePost $save): AdminPostResource
    {
        return new AdminPostResource($save($post, $request->validated()));
    }

    public function publish(Post $post, PublishPost $publish): AdminPostResource
    {
        abort_if($post->isPublished(), 409, 'Yozuv allaqachon chop etilgan.');

        return new AdminPostResource($publish($post));
    }

    public function apply(Post $post, ApplyPendingChanges $apply): AdminPostResource
    {
        abort_unless($post->isPublished() && $post->hasPending(), 409, 'Kutilayotgan oʻzgarish yoʻq.');

        return new AdminPostResource($apply($post));
    }

    public function discard(Post $post, DiscardPendingChanges $discard): AdminPostResource
    {
        return new AdminPostResource($discard($post));
    }

    public function unpublish(Post $post, UnpublishPost $unpublish): AdminPostResource
    {
        return new AdminPostResource($unpublish($post));
    }

    public function destroy(Post $post): Response
    {
        $post->delete();

        return response()->noContent();
    }

    public function preview(PreviewRequest $request): JsonResponse
    {
        return response()->json(['html' => Markdown::render($request->validated('body'))['html']]);
    }

    public function upload(UploadCoverRequest $request): JsonResponse
    {
        $path = $request->file('file')->store('covers', 'public');

        return response()->json(['url' => Post::resolveCoverUrl($path), 'path' => $path], 201);
    }
}
