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
