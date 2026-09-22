# laravel-docker-app

## セットアップ手順

### 1. コンテナを起動する
\`\`\`bash
docker compose up -d
\`\`\`

### 2. Laravelをインストールする
\`\`\`bash
docker compose exec app bash
composer create-project laravel/laravel .
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
exit
\`\`\`

### 3. .envファイルのデータベース設定
`.env` 内の以下を編集する:
\`\`\`
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret
\`\`\`

### 4. マイグレーションを実行する
\`\`\`bash
docker compose exec app php artisan migrate
\`\`\`

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


## イベント予約システムについて

### 機能

- イベント一覧表示(ページネーション付き)
- イベント詳細表示(予約フォーム・予約一覧を統合)
- イベント登録・編集・削除
- 予約作成(名前・メールアドレス・人数)
- 予約一覧表示(イベント詳細ページ内)
- 予約キャンセル
- バリデーション(メールアドレス形式・人数の数値チェックなど)

### テーブル定義

#### events テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| title | varchar | イベント名 |
| description | text | 説明 |
| event_date | datetime | 開催日時 |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

#### reservations テーブル

| カラム名 | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー(自動採番) |
| event_id | bigint | どのイベントへの予約か(events.idへの外部キー) |
| name | varchar | 予約者名 |
| email | varchar | メールアドレス |
| people | integer | 予約人数 |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### スクリーンショット

#### イベント一覧

#### イベント詳細(予約フォーム・予約一覧)
