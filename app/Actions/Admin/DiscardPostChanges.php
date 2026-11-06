<?php

namespace App\Actions\Admin;

use App\Models\Post;

class DiscardPostChanges
{
    public function __invoke(Post $post): Post
    {
        $post->pending = null;
        $post->save();
        $post->touch();

        return $post;
    }
}
