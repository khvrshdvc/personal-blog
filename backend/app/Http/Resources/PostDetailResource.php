<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PostDetailResource extends PostResource
{
    public function __construct(private array $data)
    {
        parent::__construct($data['post']);
    }

    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'body_html' => $this->data['html'],
            'toc' => $this->data['toc'],
            'prev' => $this->data['prev'] ? new PostResource($this->data['prev']) : null,
            'next' => $this->data['next'] ? new PostResource($this->data['next']) : null,
        ];
    }
}
