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
