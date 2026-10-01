<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request; // 【追加】リクエストの中身を受け取るため

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

    // 【追加】POST /api/posts に対応するメソッド
    public function store(Request $request)
    {
        // 1. 入力チェック。ルールに合わなければ、Laravelが自動で
        //    422エラー(どの項目がダメかの理由つきJSON)を返して、ここで処理が止まる
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string'],
            'category' => ['required', 'string', 'max:50'],
        ]);

        // 2. $request->user():送られてきたトークンから特定した「投稿者」
        //    保存の処理は Week 9 で作った PostService にそのまま任せる
        $post = $this->postService->createPost($request->user(), $validated);

        // 3. 投稿者名も返せるように user を読み込んでから、PostResource で変換して返す
        //    新しく作ったデータの場合、Laravelが自動でステータス201(作成成功)にしてくれる
        return new PostResource($post->load('user'));
    }
}
