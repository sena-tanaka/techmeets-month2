<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    // 一覧：S3の images フォルダにある画像のURLを取得して表示する
    public function index()
    {
        $disk = Storage::disk('s3');

        $images = collect($disk->files('images'))
            ->map(fn ($path) => [
                'path' => $path,
                // バケットは非公開なので、10分間だけ有効な署名付きURLを発行する
                'url' => $disk->temporaryUrl($path, now()->addMinutes(10)),
            ]);

        return view('images.index', compact('images'));
    }

    // アップロード：フォームから受け取った画像をS3に保存する
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ]);

        // S3の images フォルダに、重複しないファイル名で保存する
        Storage::disk('s3')->putFile('images', $request->file('image'));

        return redirect()->route('images.index')
            ->with('status', '画像をアップロードしました');
    }
}
