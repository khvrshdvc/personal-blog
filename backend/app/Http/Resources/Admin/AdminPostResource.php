<?php

namespace App\Http\Resources\Admin;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class AdminPostResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'tag' => $this->tag,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'excerpt' => $this->excerpt,
            'cover' => $this->cover,
            'body' => $this->body,
            'published_at' => $this->published_at?->toDateString(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'pending' => $this->pending,
            'words' => $this->viewWords(),
        ];
    }
}
