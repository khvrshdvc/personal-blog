<?php

namespace App\Http\Resources\Admin;

use App\Models\Post;
use App\Support\Markdown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostBriefResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $view = $this->view();

        return [
            'id' => $this->id,
            'title' => $view['title'],
            'excerpt' => $view['excerpt'] !== '' ? $view['excerpt'] : Markdown::excerpt($view['body']),
            'tag' => $view['tag'],
            'status' => $this->stateKey(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
