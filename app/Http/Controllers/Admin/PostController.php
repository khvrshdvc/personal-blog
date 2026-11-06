<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DiscardPostChanges;
use App\Actions\Admin\PostState;
use App\Actions\Admin\PublishPost;
use App\Actions\Admin\SavePost;
use App\Actions\Admin\UnpublishPost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewRequest;
use App\Http\Requests\Admin\PublishPostRequest;
use App\Http\Requests\Admin\SavePostRequest;
use App\Http\Requests\Admin\UploadCoverRequest;
use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', ['posts' => $this->list(), 'active' => null]);
    }

    public function create(?string $category = null): View
    {
        return view('admin.posts.editor', [
            'posts' => $this->list(),
            'active' => null,
            'config' => $this->config(null, $category ?? 'fikr'),
        ]);
    }

    public function edit(Post $post, PostState $state): View
    {
        return view('admin.posts.editor', [
            'posts' => $this->list(),
            'active' => $post->id,
            'config' => $this->config($state($post), $post->category->value),
        ]);
    }

    public function store(SavePostRequest $request, SavePost $save, PostState $state): JsonResponse
    {
        $post = $save(null, $request->validated());

        return response()->json(['post' => $post ? $state($post, $request->input('slug')) : null]);
    }

    public function update(SavePostRequest $request, Post $post, SavePost $save, PostState $state): JsonResponse
    {
        return response()->json(['post' => $state($save($post, $request->validated()), $request->input('slug'))]);
    }

    public function publish(PublishPostRequest $request, Post $post, PublishPost $publish, PostState $state): JsonResponse
    {
        $wasPublished = $post->isPublished();

        if ($wasPublished && ! $post->hasPending()) {
            return response()->json(['post' => $state($post), 'message' => 'Oʻzgarish yoʻq', 'changed' => false]);
        }

        $post = $publish($post, $request->boolean('strict'));

        return response()->json([
            'post' => $state($post),
            'message' => $wasPublished ? 'Yozuv yangilandi' : 'Yozuv chop etildi',
            'changed' => true,
        ]);
    }

    public function discard(Post $post, DiscardPostChanges $discard, PostState $state): JsonResponse
    {
        return response()->json(['post' => $state($discard($post)), 'message' => 'Oʻzgarishlar bekor qilindi']);
    }

    public function unpublish(Post $post, UnpublishPost $unpublish, PostState $state): JsonResponse
    {
        return response()->json(['post' => $state($unpublish($post)), 'message' => 'Yozuv qoralamaga olindi']);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json(['redirect' => route('admin.posts.index'), 'message' => 'Yozuv oʻchirildi']);
    }

    public function cover(UploadCoverRequest $request): JsonResponse
    {
        $path = $request->file('cover')->store('covers', 'public');

        return response()->json(['path' => $path, 'url' => Post::resolveCoverUrl($path)]);
    }

    public function preview(PreviewRequest $request): JsonResponse
    {
        return response()->json(['html' => Markdown::render($request->input('body'))['html']]);
    }

    private function list()
    {
        return Post::orderByDesc('updated_at')->get();
    }

    private function config(?array $state, string $category): array
    {
        $category = array_key_exists($category, config('site.categories')) ? $category : 'fikr';

        return [
            'post' => $state,
            'defaultCategory' => $category,
            'today' => now()->toDateString(),
            'tags' => array_map(fn ($c) => $c['tags'], config('site.categories')),
            'colors' => config('site.tag_colors'),
            'urls' => [
                'store' => route('admin.posts.store'),
                'cover' => route('admin.posts.cover'),
                'preview' => route('admin.posts.preview'),
                'list' => route('admin.posts.index'),
                'edit' => url('/admin/posts'),
                'site' => url('/post'),
            ],
        ];
    }
}
