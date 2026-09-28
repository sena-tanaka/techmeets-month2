# laravel-docker-app

## セットアップ手順

### 1. コンテナを起動する
```bash
docker compose up -d
```

### 2. Laravelをインストールする
```bash
docker compose exec app bash
composer create-project laravel/laravel .
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
exit
```

### 3. .envファイルのデータベース設定
`.env` 内の以下を編集する:
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret
```

### 4. マイグレーションを実行する
```bash
docker compose exec app php artisan migrate
```

### 5. 動作確認
- Laravelアプリ: http://localhost
- phpMyAdmin: http://localhost:8080 （ユーザー名: root / パスワード: secret）

---

## ブログシステムについて

### 機能

- 投稿一覧表示(ページネーション付き)
- 投稿詳細表示
- 投稿作成(タイトル・内容・カテゴリー)
- 投稿編集
- 投稿削除
- バリデーション(タイトル・内容・カテゴリーの入力チェック)
- Bladeレイアウト継承(共通レイアウトを各ページで使い回し)

### テーブル定義

#### posts テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| title | varchar | タイトル |
| content | text | 本文 |
| category | varchar | カテゴリー |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### スクリーンショット

#### 投稿一覧

#### 新規投稿フォーム

#### 投稿詳細

---

# Week 9 練習課題2: Fat Controllerのリファクタリング

Week 8で作成した会員制ブログの `PostController` を、Repository/Serviceパターンと FormRequest を使ってリファクタリングしました。

## Before（Week 8）

コントローラーが「バリデーション」「DB操作」「認可」「画面の返却」をすべて担当していました。

```php
public function store(Request $request)
{
    // バリデーション（updateにも同じルールを重複して記述）
    $validated = $request->validate([
        'title'    => ['required', 'string', 'max:255'],
        'content'  => ['required', 'string', 'max:10000'],
        'category' => ['nullable', 'string', 'max:50'],
    ]);

    // DB操作をコントローラーで直接実行
    $request->user()->posts()->create($validated);

    return redirect()->route('posts.index')->with('success', '投稿しました');
}
```

問題点:

- バリデーションのルールが `store` と `update` に重複している
- `Post::with('user')->latest()->get()` や `->create()` など、DB操作がコントローラーに直接書かれている
- DBがないとコントローラーの処理を確認できず、テストしにくい

## After（Week 9）

```php
public function store(PostRequest $request)
{
    $this->postService->createPost($request->user(), $request->validated());

    return redirect()->route('posts.index')->with('success', '投稿しました');
}
```

コントローラーは「リクエストを受け取り、Serviceに渡し、画面を返す」だけになりました。

## 責務の分け方

| クラス | 役割 |
| --- | --- |
| `PostController` | リクエストを受け取り、レスポンスを返す |
| `PostRequest` | バリデーション（store/updateで共通化） |
| `PostService` | ビジネスロジック（ログインユーザーの投稿として作成する、など） |
| `PostRepository` | DB操作（Eloquentの処理はここだけに書く） |
| `PostPolicy` | 認可（自分の投稿だけ編集・削除できる） |

## 変更による効果

- **重複の解消**: バリデーションのルールが `PostRequest` の1か所にまとまった
- **変更に強い**: 一覧にページネーションを追加したとき、`PostRepository` と `PostService` の変更だけで済み、`PostController` は1行も変更しなかった
- **テストしやすい**: Serviceは Repository をモックに差し替えればDBなしでテストでき、Policyは User と Post を渡すだけでテストできる

