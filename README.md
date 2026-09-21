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
