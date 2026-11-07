<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'repo' => $this->repo,
            'description' => $this->description,
            'stack' => $this->stack ?? [],
            'demo_url' => $this->demo_url,
        ];
    }
}
