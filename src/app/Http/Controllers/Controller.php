<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    // 一覧（誰でも見られる）
    public function index()
    {
        // 【3】投稿者の情報もまとめて取得
        $posts = Post::with('user')->latest()->get();
        return view('posts.index', compact('posts'));
    }

    // 詳細（誰でも見られる）
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // 作成画面（ログインユーザーのみ）
    public function create()
    {
        return view('posts.create');
    }

    // 保存（ログインユーザーのみ）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        // 【1】ログインユーザーの user_id を自動でセット
        $request->user()->posts()->create($validated);

        return redirect()->route('posts.index')->with('success', '投稿しました');
    }

    // 編集画面（自分の投稿のみ）
    public function edit(Post $post)
    {
        // 【2】自分の投稿でなければ 403
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    // 更新（自分の投稿のみ）
    public function update(Request $request, Post $post)
    {
        // 【2】自分の投稿でなければ 403
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)->with('success', '更新しました');
    }

    // 削除（自分の投稿のみ）
    public function destroy(Post $post)
    {
        // 【2】自分の投稿でなければ 403
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')->with('success', '削除しました');
    }
}
