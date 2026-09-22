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
