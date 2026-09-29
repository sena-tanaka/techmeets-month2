<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\PostService;

class PostController extends Controller
{
    // コンストラクタでPostServiceを受け取る(Laravelが自動で用意してくれる=依存性注入)
    public function __construct(private PostService $postService)
    {
    }

    // GET /api/posts に対応するメソッド
    public function index()
    {
        // 1. Serviceに記事一覧を取ってきてもらう
        $posts = $this->postService->getAllPostsForApi();

        // 2. 複数件なので collection() を使い、各記事をPostResourceの形に変換して返す
        //    → Laravelが自動でJSONレスポンスにしてくれる
        return PostResource::collection($posts);
    }
}
