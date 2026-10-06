# 会員制ブログ（Laravel Breeze）

Laravel Breeze の認証機能を使った会員制ブログです。

## 機能一覧

- ユーザー登録・ログイン・ログアウト（Breeze）
- 記事一覧・詳細の表示（誰でも閲覧可能）
- 記事の投稿（ログインユーザーのみ）
- 記事の編集・削除（投稿者本人のみ）
- プロフィールの編集・パスワード変更・退会（Breeze）

## 実装したセキュリティ対策

| 脅威 | 対策 |
|---|---|
| 未ログインでの投稿 | `auth` ミドルウェアでログイン画面へリダイレクト |
| 他人の記事の改ざん | `PostPolicy` と `Gate::authorize()` で 403、ボタンも `@can` で非表示 |
| なりすまし投稿 | `user_id` はログインユーザーから設定し、`$fillable` に含めない |
| XSS | `{{ }}` による自動エスケープ、本文は `nl2br(e())` で表示 |
| CSRF | 全フォームに `@csrf` |
| SQLインジェクション | Eloquent のみ使用 |
| 不正な入力 | バリデーション（必須・文字数上限） |
| パスワード漏洩 | bcrypt によるハッシュ化（Breeze 標準） |
| ブルートフォース攻撃 | ログイン試行回数の制限（Breeze 標準） |

## 使用技術

- PHP / Laravel / Laravel Breeze（Blade）
- MySQL
- Docker（nginx・PHP-FPM・MySQL・phpMyAdmin）

## セットアップ手順

```bash
# 1. リポジトリをクローン
git clone https://github.com/sena-tanaka/techmeets-month2.git
cd techmeets-month2

# 2. コンテナを起動
docker-compose up -d

# 3. 依存パッケージをインストール
docker-compose exec app composer install

# 4. 環境設定ファイルを作成してアプリキーを生成
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate

# 5. マイグレーションを実行
docker-compose exec app php artisan migrate

# 6. フロントエンドをビルド（src フォルダで実行）
cd src
npm install
npm run build
```

`.env` のデータベース設定は次のとおりです。

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret
```

## 使い方

1. ブラウザで http://localhost にアクセス
2. 右上の「新規登録」からユーザーを作成
3. 「ブログ」メニューから記事の投稿・編集・削除ができます

phpMyAdmin は http://localhost:8080 から利用できます。

## テーブル定義

### posts テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| user_id | bigint | 投稿者のユーザーID(usersテーブルの外部キー) |
| title | varchar | タイトル |
| content | text | 本文 |
| category | varchar | カテゴリー |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

---

## 商品管理システムについて

### 機能

- 商品一覧表示(ページネーション付き)
- 商品詳細表示
- 商品登録(商品名・価格・説明・在庫数・カテゴリー)
- 商品編集
- 商品削除
- バリデーション(数値項目のチェックなど)

### テーブル定義

#### products テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| name | varchar | 商品名 |
| price | decimal(10,2) | 価格 |
| description | text | 説明 |
| stock | integer | 在庫数 |
| category | varchar | カテゴリー |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### スクリーンショット

#### 商品一覧

#### 商品登録フォーム

---

## Week 9 基本課題: Repository/Service層の実装

ブログアプリを Repository/Service パターンでリファクタリングしました。

| クラス | 役割 |
| --- | --- |
| `PostController` | リクエストを受け取り、レスポンスを返す |
| `PostRequest` | バリデーション（store/updateで共通化） |
| `PostService` | ビジネスロジック |
| `PostRepository` | DB操作（Eloquentの処理はここだけに書く） |
| `PostPolicy` | 認可（自分の投稿だけ編集・削除できる） |

---

## Week 9 練習課題2: Fat Controllerのリファクタリング

Week 8で作成した会員制ブログの `PostController` を、Repository/Serviceパターンと FormRequest を使ってリファクタリングしました。

### Before（Week 8）

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

### After（Week 9）

```php
public function store(PostRequest $request)
{
    $this->postService->createPost($request->user(), $request->validated());

    return redirect()->route('posts.index')->with('success', '投稿しました');
}
```

コントローラーは「リクエストを受け取り、Serviceに渡し、画面を返す」だけになりました。

### 変更による効果

- **重複の解消**: バリデーションのルールが `PostRequest` の1か所にまとまった
- **変更に強い**: 一覧にページネーションを追加したとき、`PostRepository` と `PostService` の変更だけで済み、`PostController` は1行も変更しなかった
- **テストしやすい**: Serviceは Repository をモックに差し替えればDBなしでテストでき、Policyは User と Post を渡すだけでテストできる

---

## Week 9 練習課題1: タスク管理アプリ

最初から Repository/Service パターンで構築したタスク管理アプリです（http://localhost/tasks）。

- ログインユーザーが自分のタスクだけを管理（一覧・作成・詳細・編集・削除）
- 完了/未完了の切り替え（判断が入る処理なので `TaskService::toggleCompletion` に配置）
- 他人のタスクは一覧に表示されず、URLで直接アクセスしても `TaskPolicy` で403

### tasks テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| user_id | bigint | 持ち主のユーザーID(usersテーブルの外部キー) |
| title | varchar | タイトル |
| description | text | 説明 |
| due_date | date | 期限 |
| is_completed | boolean | 完了フラグ |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |
