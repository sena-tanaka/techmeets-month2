# techmeets-month2 — Week11 AWSデプロイ

Week10 で作成した Laravel + Docker のブログアプリ（`week10/post-form` ブランチ）を AWS にデプロイし、S3 への画像アップロード機能を追加しました。

- アプリURL: http://57.183.31.139
- 画像アップロード（S3）: http://57.183.31.139/images（要ログイン）

---

## 1. システム構成

```
                 ┌──────────────── VPC（ap-northeast-1 / 東京）────────────────┐
                 │                                                              │
ブラウザ ──HTTP:80──▶ EC2（Ubuntu / Docker）                                   │
                 │     ├─ nginx コンテナ（:80）                                  │
                 │     └─ app コンテナ（PHP-FPM / Laravel :9000 ※外部非公開）     │
                 │            │                                                 │
                 │            └──MySQL:3306──▶ RDS（MySQL / パブリックアクセスなし）│
                 └──────────────────────────────────────────────────────────────┘
                              │
                              └──HTTPS（AWS SDK）──▶ S3（非公開バケット）

管理者PC ──SSH:22（自分のIPのみ）──▶ EC2
```

| リソース | 設定 | 備考 |
|---|---|---|
| EC2 | t3.micro / Ubuntu 26.04 LTS / ストレージ 20GiB | 課題指定は t2.micro・Ubuntu 22.04 だが、作成時点のクイックスタートと無料利用枠の標準に合わせた |
| RDS | MySQL / db.t3.micro / パブリックアクセスなし | |
| S3 | `sena-techmeets-images-2026`（東京）/ パブリックアクセスをすべてブロック | 画像は署名付きURLで表示 |
| IAM | ユーザー `laravel-s3-uploader`（コンソールログインなし） | 上記バケット専用の最小権限ポリシー |

---

## 2. デプロイ手順

### 2-1. EC2 の作成と初期設定

1. EC2 インスタンスを作成（東京リージョン、t3.micro、Ubuntu、キーペア RSA/.pem、ストレージ 20GiB）
2. セキュリティグループで SSH(22) を自分のIPのみ、HTTP(80) を全許可に設定（理由は「3. セキュリティグループの設計」）
3. SSH で接続

```powershell
ssh -i $HOME\.ssh\laravel-key.pem ubuntu@<EC2のパブリックIP>
```

4. Docker のインストールとスワップ追加（t3.micro はメモリ 1GB のため、composer / npm の実行時のメモリ不足対策）

```bash
sudo apt update
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker ubuntu   # 反映のため一度ログアウトして再接続

sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

### 2-2. RDS の作成

1. MySQL / 無料利用枠テンプレート / db.t3.micro で作成
2. 「EC2 コンピューティングリソースに接続」で上記 EC2 を指定（EC2 からのみ 3306 を許可するセキュリティグループが自動作成される）
3. パブリックアクセスは「なし」
4. EC2 から `laravel` データベースを作成

```bash
docker run --rm -it mysql:8.0 mysql -h <RDSのエンドポイント> -u admin -p \
  -e "CREATE DATABASE IF NOT EXISTS laravel;"
```

### 2-3. アプリの配置と起動

```bash
git clone https://github.com/sena-tanaka/techmeets-month2.git
cd techmeets-month2
git checkout week10/post-form

cp src/.env.example src/.env
nano src/.env   # 下記の値を設定
```

`src/.env`（本番用。Git 管理外）

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<EC2のパブリックIP>

DB_CONNECTION=mysql
DB_HOST=<RDSのエンドポイント>
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=admin
DB_PASSWORD=<RDSのマスターパスワード>

AWS_ACCESS_KEY_ID=<IAMユーザーのアクセスキーID>
AWS_SECRET_ACCESS_KEY=<IAMユーザーのシークレットアクセスキー>
AWS_DEFAULT_REGION=ap-northeast-1
AWS_BUCKET=sena-techmeets-images-2026
AWS_USE_PATH_STYLE_ENDPOINT=false
```

本番では DB に RDS を使うため、`db`・`phpmyadmin` コンテナを含まない `docker-compose.prod.yml` を使用します。

```bash
echo 'export COMPOSE_FILE=docker-compose.prod.yml' >> ~/.bashrc
source ~/.bashrc

docker compose up -d --build
docker compose exec app composer install --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker run --rm -v $(pwd)/src:/app -w /app node:22 sh -c "npm ci && npm run build"
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

ブラウザで `http://<EC2のパブリックIP>` にアクセスして表示を確認します。

### 2-4. S3 画像アップロードの設定

1. S3 バケットを作成（東京リージョン、パブリックアクセスをすべてブロック）
2. IAM ユーザーを作成し、インラインポリシー（「4-2. IAM の設計」）を付与してアクセスキーを発行
3. ライブラリを追加（ローカルで実行し、`composer.json` / `composer.lock` をコミット）

```bash
docker compose exec app composer require league/flysystem-aws-s3-v3 "^3.0"
```

4. nginx のアップロード上限を 2MB に変更（`docker/nginx/default.conf` に `client_max_body_size 2M;`）
5. EC2 で反映

```bash
git pull
docker compose exec app composer install --optimize-autoloader
docker compose exec app php artisan config:clear
docker compose restart nginx
```

---

## 3. セキュリティグループの設計

方針: **「必要な通信だけを、必要な相手にだけ許可する」**。何も書いていない通信はすべて拒否される（セキュリティグループはホワイトリスト方式）ので、ルールを1つ追加するごとに「なぜ必要か」「誰に許可するか」を決めて設定しました。

### 3-1. EC2 用（`launch-wizard-2`）

| タイプ | ポート | ソース | 理由 |
|---|---|---|---|
| SSH | 22 | 自分のIP（`/32`） | サーバー管理用。管理者以外が接続する必要はない |
| HTTP | 80 | `0.0.0.0/0` | 公開Webアプリのため、誰でもアクセスできる必要がある |

#### SSH（22）を「自分のIPのみ」にした理由

- SSH はサーバーを丸ごと操作できる入口なので、攻撃者に最も狙われるポートの1つ。全世界に開けると、パスワード総当たりや脆弱性を狙った接続試行を受け続ける
- 鍵認証（.pem）だけでも守られているが、IP 制限を重ねることで「鍵が漏れても、許可したIP以外からは接続できない」という二重の防御にした
- `/32` は「このIPアドレス1つだけ」という意味で、範囲を最小にしている
- **運用上の注意**: 接続場所（Wi-Fi・テザリングなど）が変わると自分のIPも変わり、SSH が `Connection timed out` になる。実際に作業2日目にIPが変わって接続できなくなったため、`https://checkip.amazonaws.com` で現在のIPを確認し、ルールを「マイIP」に更新して解決した

#### HTTP（80）を「全許可」にした理由

- ブログは不特定多数の閲覧者に公開するアプリなので、送信元を絞ることができない
- 外部に公開しているのは nginx だけで、PHP-FPM（9000）や MySQL（3306）はインターネットに公開していない
- 投稿・画像アップロードなどの操作はアプリ側のログイン認証で保護している

### 3-2. EC2 ⇔ RDS 間（RDS 作成時に自動作成）

| セキュリティグループ | 付与先 | タイプ | ポート | 送信先 / 送信元 |
|---|---|---|---|---|
| `ec2-rds-1` | EC2 | MySQL/Aurora（アウトバウンド） | 3306 | `rds-ec2-1` |
| `rds-ec2-1` | RDS | MySQL/Aurora（インバウンド） | 3306 | `ec2-rds-1` |

#### RDS（3306）を「EC2 からのみ」にした理由

- データベースに直接アクセスする必要があるのはアプリ（EC2）だけ。利用者はブラウザから EC2 経由で操作するので、DB をインターネットに出す理由がない
- 送信元を IP アドレスではなく**セキュリティグループで指定**しているため、EC2 のIPが変わっても設定を変える必要がなく、このセキュリティグループを付けたインスタンス以外からは接続できない
- さらに RDS 自体を「パブリックアクセスなし」にしているので、仮にルールを誤って広げても、インターネットから到達できない（二重の防御）

### 3-3. あえて開けていないポート

| ポート | 用途 | 開けていない理由 |
|---|---|---|
| 443（HTTPS） | 暗号化通信 | 独自ドメインと SSL 証明書がまだないため。本番運用では HTTPS 化が必須で、今後の課題 |
| 3306（MySQL）の外部公開 | DB 接続 | 上記のとおり EC2 からのみで十分 |
| 8080（phpMyAdmin） | DB 管理画面 | DB を直接操作できる画面は攻撃対象になりやすいため、本番構成（`docker-compose.prod.yml`）から phpMyAdmin 自体を削除した |
| 9000（PHP-FPM） | nginx → PHP | Docker の内部ネットワークで通信するだけなので、ホストにもインターネットにも公開していない |

### 3-4. 実際に観測した不審なアクセス

公開して1日ほどで、アプリのログに自分がアクセスしていない URL へのリクエストが記録されていました。

```
"GET /stalker_portal/server/tools/auth_simple.php" 404
```

これはインターネット上のボットが、脆弱なソフトウェアが動いていないかを手当たり次第に探しているアクセスです。公開したサーバーはすぐに探索の対象になることを実感し、「必要なポート以外は開けない」「管理用のポートは送信元を絞る」という設計の重要性を確認しました。

---

## 4. S3 / IAM の設計

### 4-1. S3 バケットを非公開にした理由

- バケットは「パブリックアクセスをすべてブロック」のまま運用している
- 画像の表示には `Storage::disk('s3')->temporaryUrl()` で発行する**有効期限10分の署名付きURL**を使用。URL が外部に漏れても期限が切れれば見られなくなる
- 署名のない通常の URL（`https://<バケット>.s3.ap-northeast-1.amazonaws.com/images/...`）でアクセスすると `AccessDenied` になることを確認済み

### 4-2. IAM の設計（最小権限）

アプリ専用の IAM ユーザー `laravel-s3-uploader` を作成し、コンソールへのログインは許可していません。権限は次のインラインポリシーのみです。

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:PutObject",

     