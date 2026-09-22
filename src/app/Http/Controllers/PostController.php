<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // 1. 投稿一覧を表示
    public function index()
    {
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    // 投稿作成フォームを表示
    public function create()
    {
        return view('posts.create');
    }

    // 2. 投稿を保存する
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category' => 'required|max:50',
        ]);

        Post::create($validated);

        return redirect()->route('posts.index')->with('success', '投稿しました');
    }

    // 3. 投稿詳細を表示
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // 編集フォームを表示
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // 4. 投稿の更新を保存する
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'category' => 'required|max:50',
        ]);

        $post->update($validated);

        return redirect()->route('posts.index')->with('success', '更新しました');
    }

    // 5. 投稿を削除する
    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', '削除しました');
    }
}
