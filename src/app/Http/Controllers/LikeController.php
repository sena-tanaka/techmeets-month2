<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {
    }

    // いいね/取り消しの切り替え
    public function toggle(Request $request, Post $post)
    {
        $this->postService->toggleLike($post, $request->user());

        return back();
    }
}
