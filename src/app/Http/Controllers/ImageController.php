<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageController extends Controller
{
    // 一覧：S3の images フォルダにある画像を、CloudFront経由のURLで表示する
    public function index()
    {
        $disk          = Storage::disk('s3');
        $cloudfrontUrl = rtrim(config('services.cloudfront.url'), '/');

        $images = collect($disk->files('images'))
            ->map(fn ($path) => [
                'path' => $path,
                // CloudFrontが設定されていればCDNのURL、なければ従来の署名付きURL
                'url'  => $cloudfrontUrl
                    ? "{$cloudfrontUrl}/{$path}"
                    : $disk->temporaryUrl($path, now()->addMinutes(10)),
            ]);

        return view('images.index', compact('images'));
    }

    // アップロード：画像をWebPに変換してからS3に保存する
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ]);

        $file = $request->file('image');

        // ① GDを使って画像を読み込む
        $manager = new ImageManager(new Driver());
        $image   = $manager->read($file->getRealPath());

        // ② 横幅が1600pxより大きい場合だけ縮小する(小さい画像は拡大しない)
        $image->scaleDown(width: 1600);

        // ③ WebP形式(画質80)に変換する
        $webp = (string) $image->toWebp(quality: 80);

        // ④ 重複しないファイル名(.webp)でS3に保存する
        $path = 'images/' . Str::uuid() . '.webp';
        Storage::disk('s3')->put($path, $webp, [
            'ContentType' => 'image/webp',
        ]);

        // 変換前後のサイズを表示して、効果を確認できるようにする
        $before = round($file->getSize() / 1024, 1);
        $after  = round(strlen($webp) / 1024, 1);

        return redirect()->route('images.index')
            ->with('status', "WebPに変換してアップロードしました({$before}KB → {$after}KB)");
    }
}
