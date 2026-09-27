<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    // 一覧（誰でも見られる）
    public function index()
    {
        $posts = $this->postService->getPosts();
        return view('member.index', compact('posts'));
    }

    // 詳細（誰でも見られる）
    public function show(Post $post)
    {
        return view('member.show', compact('post'));
    }

    // 作成画面（ログインユーザーのみ）
    public function create()
    {
        return view('member.create');
    }

    // 保存（ログインユーザーのみ）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $this->postService->createPost($request->user(), $validated);

        return redirect()->route('posts.index')->with('success', '投稿しました');
    }

    // 編集画面（自分の投稿のみ）
    public function edit(Post $post)
    {
        Gate::authorize('update', $post);

        return view('member.edit', compact('post'));
    }

    // 更新（自分の投稿のみ）
    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $this->postService->updatePost($post, $validated);

        return redirect()->route('posts.show', $post)->with('success', '更新しました');
    }

    // 削除（自分の投稿のみ）
    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $this->postService->deletePost($post);

        return redirect()->route('posts.index')->with('success', '削除しました');
    }
}
