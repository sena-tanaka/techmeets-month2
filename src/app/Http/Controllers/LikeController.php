<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        // いいねしていなければ追加、していれば削除
        $post->likes()->toggle($request->user()->id);

        return back();
    }
}
