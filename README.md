

Readme
 · 
MD
techmeets-month2 — Week13 独自ドメイン + HTTPS 化
公開URL：https://sena-blog-app.jp

構成
ブラウザ
  ↓ (80 / 443)
EC2ホストのNginx（HTTPS終端・リダイレクト）
  ↓ proxy_pass http://127.0.0.1:8000
Dockerのnginxコンテナ
  ↓
app（PHP-FPM / Laravel） → db（MySQL）
ドメイン：sena-blog-app.jp（ムームードメインで取得、ムームーDNSのカスタム設定で @ と www のAレコードにElastic IPを登録）
証明書：Let's Encrypt（Certbot）。sena-blog-app.jp と www.sena-blog-app.jp の両方を含む
Dockerのnginxコンテナは 127.0.0.1:8000 でのみ待ち受け、外部からはホストのNginx経由でしかアクセスできないようにしている
Laravel側は bootstrap/app.php で trustProxies を設定し、ホストのNginxから届く X-Forwarded-* ヘッダーを信頼している
ホスト側のNginx設定（/etc/nginx/conf.d/app.conf）
nginx
# HTTP（wwwあり・なし）→ https://sena-blog-app.jp
server {
    listen 80;
    server_name sena-blog-app.jp www.sena-blog-app.jp;
    return 301 https://sena-blog-app.jp$request_uri;
}

# https://www → https://sena-blog-app.jp
server {
    listen 443 ssl;
    server_name www.sena-blog-app.jp;
    ssl_certificate     /etc/letsencrypt/live/sena-blog-app.jp/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sena-blog-app.jp/privkey.pem;
    include             /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam         /etc/letsencrypt/ssl-dhparams.pem;
    return 301 https://sena-blog-app.jp$request_uri;
}

# 本体：Dockerのnginxコンテナへリバースプロキシ
server {
    listen 443 ssl;
    server_name sena-blog-app.jp;
    ssl_certificate     /etc/letsencrypt/live/sena-blog-app.jp/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sena-blog-app.jp/privkey.pem;
    include             /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam         /etc/letsencrypt/ssl-dhparams.pem;
    client_max_body_size 10M;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host  $host;
        proxy_set_header X-Forwarded-Port  $server_port;
    }
}
設定	役割
listen 80 の return 301	HTTPでのアクセスを、wwwの有無にかかわらず https://sena-blog-app.jp へ恒久リダイレクトする
server_name www.sena-blog-app.jp のブロック	HTTPSでのwwwありのアクセスを、wwwなしに統一する
proxy_pass http://127.0.0.1:8000	受けたリクエストをDockerのnginxコンテナへ転送する
X-Forwarded-Proto $scheme など	元のリクエストがHTTPSだったことをLaravelに伝える（trustProxies とセットで機能する）
client_max_body_size 10M	画像アップロード用。ホストNginxのデフォルト（1MB）を広げている
コマンドによる動作確認
dig sena-blog-app.jp +short
52.193.42.53
ドメインのAレコードに登録したEC2のElastic IPが返ってきた。DNSが正しく反映され、sena-blog-app.jp という名前でこのEC2にたどり着けることを意味する。

curl -I https://sena-blog-app.jp
HTTP/1.1 200 OK
Server: nginx/1.30.5
Date: Thu, 08 Oct 2026 10:31:19 GMT
Content-Type: text/html; charset=utf-8
Connection: keep-alive
X-Powered-By: PHP/8.2.34
Cache-Control: no-cache, private
Set-Cookie: XSRF-TOKEN=...; expires=Thu, 08 Oct 2026 12:31:19 GMT; Max-Age=7200; path=/; secure; samesite=lax
Set-Cookie: laravel-session=...; expires=Thu, 08 Oct 2026 12:31:19 GMT; Max-Age=7200; path=/; secure; httponly; samesite=lax
HTTP/1.1 200 OK：HTTPSでの接続とページの取得に成功した
Server: nginx/1.30.5：応答しているのはEC2ホストのNginx（リバースプロキシ）
Content-Type: text/html; charset=utf-8：返ってきた内容がUTF-8のHTMLである
X-Powered-By: PHP/8.2.34：裏側でPHP（Laravel）が処理している
Set-Cookie: XSRF-TOKEN / laravel-session：LaravelがCSRF対策用トークンとセッションIDをCookieで発行している。secure 属性が付いているのは、LaravelがリクエストをHTTPSと正しく認識しているため（trustProxies が効いている証拠）。httponly はJavaScriptからセッションCookieを読めないようにする設定
sudo certbot certificates
Found the following certs:
  Certificate Name: sena-blog-app.jp
    Key Type: ECDSA
    Domains: sena-blog-app.jp www.sena-blog-app.jp
    Expiry Date: 2027-01-06 09:22:22+00:00 (VALID: 89 days)
    Certificate Path: /etc/letsencrypt/live/sena-blog-app.jp/fullchain.pem
    Private Key Path: /etc/letsencrypt/live/sena-blog-app.jp/privkey.pem
証明書は sena-blog-app.jp と www.sena-blog-app.jp の両方に有効で、有効期限は2027年1月6日（残り89日）。Let's Encryptの証明書は有効期間が短いため、自動更新を設定している。

自動更新の設定は以下で確認した。

$ sudo systemctl list-timers | grep certbot
Thu 2026-10-08 20:55:16 UTC 10h left  -  -  certbot-renew.timer  certbot-renew.service

$ sudo certbot renew --dry-run
Simulating renewal of an existing certificate for sena-blog-app.jp and www.sena-blog-app.jp
Congratulations, all simulated renewals succeeded:
  /etc/letsencrypt/live/sena-blog-app.jp/fullchain.pem (success)
certbot-renew.timer が登録されていて定期的に更新チェックが行われること、renew --dry-run（更新の予行演習）が成功し、実際の更新処理も問題なく動くことを確認した。

リダイレクトの確認（HTTP→HTTPS、wwwの統一）
$ curl -I http://sena-blog-app.jp
HTTP/1.1 301 Moved Permanently
Location: https://sena-blog-app.jp/

$ curl -I https://www.sena-blog-app.jp
HTTP/1.1 301 Moved Permanently
Location: https://sena-blog-app.jp/
HTTPでのアクセスと、wwwありのアクセスのどちらも 301 Moved Permanently（恒久的な移動）が返り、Location ヘッダーで https://sena-blog-app.jp/ に転送されることを確認した。

trustProxies の動作確認
$ curl -s https://sena-blog-app.jp/login | grep -oE '(action|href)="https?://[^"]*"' | head -n 5
href="https://fonts.bunny.net"
href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
href="https://sena-blog-app.jp/build/assets/app-CIaqI9WY.css"
href="https://sena-blog-app.jp/build/assets/app-WC-ZjLzv.js"
href="https://sena-blog-app.jp/build/assets/app-CIaqI9WY.css"
LaravelがCSS・JSのURLを https:// で生成していることを確認した。HTTPSはホストのNginxで終端し、Dockerのコンテナ以降はHTTPで通信しているため、trustProxies がないとLaravelは自分がHTTPでアクセスされていると判断し、http:// のURLを生成して混在コンテンツになってしまう。

trustProxies(at: '*') としているが、Dockerのnginxコンテナは 127.0.0.1:8000 でのみ待ち受けていて、外部からはホストのNginxを経由しないと届かないため、X-Forwarded-* ヘッダーを外部から偽装されることはない。

Week 11 で作成した EC2・RDS は一度削除したため、Week 13 では EC2 を作り直し、DB は RDS ではなく Docker の db コンテナを使う構成にしました。以下の Week 11 の記録にある IP アドレス（57.183.31.139）や RDS の構成は当時のものです。

techmeets-month2 — Week11 AWSデプロイ
Week10 で作成した Laravel + Docker のブログアプリ（week10/post-form ブランチ）を AWS にデプロイし、S3 への画像アップロード機能を追加しました。

アプリURL: http://57.183.31.139
画像アップロード（S3）: http://57.183.31.139/images（要ログイン）
1. システム構成
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
リソース	設定	備考
EC2	t3.micro / Ubuntu 26.04 LTS / ストレージ 20GiB	課題指定は t2.micro・Ubuntu 22.04 だが、作成時点のクイックスタートと無料利用枠の標準に合わせた
RDS	MySQL / db.t4g.micro / パブリックアクセスなし	課題指定は db.t3.micro だが、作成時点の無料利用枠の標準に合わせた
S3	sena-techmeets-images-2026（東京）/ パブリックアクセスをすべてブロック	画像は署名付きURLで表示
IAM	ユーザー laravel-s3-uploader（コンソールログインなし）	上記バケット専用の最小権限ポリシー
2. デプロイ手順
2-1. EC2 の作成と初期設定
EC2 インスタンスを作成（東京リージョン、t3.micro、Ubuntu、キーペア RSA/.pem、ストレージ 20GiB）
セキュリティグループで SSH(22) を自分のIPのみ、HTTP(80) を全許可に設定（理由は「3. セキュリティグループの設計」）
SSH で接続
powershell
ssh -i $HOME\.ssh\laravel-key.pem ubuntu@<EC2のパブリックIP>
Docker のインストールとスワップ追加（t3.micro はメモリ 1GB のため、composer / npm の実行時のメモリ不足対策）
bash
sudo apt update
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker ubuntu   # 反映のため一度ログアウトして再接続

sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
2-2. RDS の作成
MySQL / 無料利用枠テンプレート / db.t4g.micro で作成
「EC2 コンピューティングリソースに接続」で上記 EC2 を指定（EC2 からのみ 3306 を許可するセキュリティグループが自動作成される）
パブリックアクセスは「なし」
EC2 から laravel データベースを作成
bash
docker run --rm -it mysql:8.0 mysql -h <RDSのエンドポイント> -u admin -p \
  -e "CREATE DATABASE IF NOT EXISTS laravel;"
2-3. アプリの配置と起動
bash
git clone https://github.com/sena-tanaka/techmeets-month2.git
cd techmeets-month2
git checkout week10/post-form

cp src/.env.example src/.env
nano src/.env   # 下記の値を設定
src/.env（本番用。Git 管理外）

env
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
本番では DB に RDS を使うため、db・phpmyadmin コンテナを含まない docker-compose.prod.yml を使用します。

bash
echo 'export COMPOSE_FILE=docker-compose.prod.yml' >> ~/.bashrc
source ~/.bashrc

docker compose up -d --build
docker compose exec app composer install --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker run --rm -v $(pwd)/src:/app -w /app node:22 sh -c "npm ci && npm run build"
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
ブラウザで http://<EC2のパブリックIP> にアクセスして表示を確認します。

2-4. S3 画像アップロードの設定
S3 バケットを作成（東京リージョン、パブリックアクセスをすべてブロック）
IAM ユーザーを作成し、インラインポリシー（「4-2. IAM の設計」）を付与してアクセスキーを発行
ライブラリを追加（ローカルで実行し、composer.json / composer.lock をコミット）
bash
docker compose exec app composer require league/flysystem-aws-s3-v3 "^3.0"
nginx のアップロード上限を 2MB に変更（docker/nginx/default.conf に client_max_body_size 2M;）
EC2 で反映
bash
git pull
docker compose exec app composer install --optimize-autoloader
docker compose exec app php artisan config:clear
docker compose restart nginx
3. セキュリティグループの設計
方針: 「必要な通信だけを、必要な相手にだけ許可する」。何も書いていない通信はすべて拒否される（セキュリティグループはホワイトリスト方式）ので、ルールを1つ追加するごとに「なぜ必要か」「誰に許可するか」を決めて設定しました。

3-1. EC2 用（launch-wizard-X）【要確認：実際の名前】
タイプ	ポート	ソース	理由
SSH	22	自分のIP（/32）	サーバー管理用。管理者以外が接続する必要はない
HTTP	80	0.0.0.0/0	公開Webアプリのため、誰でもアクセスできる必要がある
SSH（22）を「自分のIPのみ」にした理由
SSH はサーバーを丸ごと操作できる入口なので、攻撃者に最も狙われるポートの1つ。全世界に開けると、パスワード総当たりや脆弱性を狙った接続試行を受け続ける
鍵認証（.pem）だけでも守られているが、IP 制限を重ねることで「鍵が漏れても、許可したIP以外からは接続できない」という二重の防御にした
/32 は「このIPアドレス1つだけ」という意味で、範囲を最小にしている
運用上の注意: 接続場所（Wi-Fi・テザリングなど）が変わると自分のIPも変わり、SSH が Connection timed out になる。実際に作業2日目にIPが変わって接続できなくなったため、https://checkip.amazonaws.com で現在のIPを確認し、ルールを「マイIP」に更新して解決した
HTTP（80）を「全許可」にした理由
ブログは不特定多数の閲覧者に公開するアプリなので、送信元を絞ることができない
外部に公開しているのは nginx だけで、PHP-FPM（9000）や MySQL（3306）はインターネットに公開していない
投稿・画像アップロードなどの操作はアプリ側のログイン認証で保護している
3-2. EC2 ⇔ RDS 間（RDS 作成時に自動作成）【要確認：実際の名前】
セキュリティグループ	付与先	タイプ	ポート	送信先 / 送信元
ec2-rds-X	EC2	MySQL/Aurora（アウトバウンド）	3306	rds-ec2-X
rds-ec2-X	RDS	MySQL/Aurora（インバウンド）	3306	ec2-rds-X
RDS（3306）を「EC2 からのみ」にした理由
データベースに直接アクセスする必要があるのはアプリ（EC2）だけ。利用者はブラウザから EC2 経由で操作するので、DB をインターネットに出す理由がない
送信元を IP アドレスではなくセキュリティグループで指定しているため、EC2 のIPが変わっても設定を変える必要がなく、このセキュリティグループを付けたインスタンス以外からは接続できない
さらに RDS 自体を「パブリックアクセスなし」にしているので、仮にルールを誤って広げても、インターネットから到達できない（二重の防御）
3-3. あえて開けていないポート
ポート	用途	開けていない理由
443（HTTPS）	暗号化通信	独自ドメインと SSL 証明書がまだないため。本番運用では HTTPS 化（ALB + ACM など）が必須で、今後の課題
3306（MySQL）の外部公開	DB 接続	上記のとおり EC2 からのみで十分
8080（phpMyAdmin）	DB 管理画面	DB を直接操作できる画面は攻撃対象になりやすいため、本番構成（docker-compose.prod.yml）から phpMyAdmin 自体を削除した
9000（PHP-FPM）	nginx → PHP	Docker の内部ネットワークで通信するだけなので、ホストにもインターネットにも公開していない
3-4. 実際に観測した不審なアクセス
公開して1日ほどで、アプリのログに自分がアクセスしていない URL へのリクエストが記録されていました。

"GET /stalker_portal/server/tools/auth_simple.php" 404
これはインターネット上のボットが、脆弱なソフトウェアが動いていないかを手当たり次第に探しているアクセスです。公開したサーバーはすぐに探索の対象になることを実感し、「必要なポート以外は開けない」「管理用のポートは送信元を絞る」という設計の重要性を確認しました。

4. S3 / IAM の設計
4-1. S3 バケットを非公開にした理由
バケットは「パブリックアクセスをすべてブロック」のまま運用している
画像の表示には Storage::disk('s3')->temporaryUrl() で発行する有効期限10分の署名付きURLを使用。URL が外部に漏れても期限が切れれば見られなくなる
署名のない通常の URL（https://<バケット>.s3.ap-northeast-1.amazonaws.com/images/...）でアクセスすると AccessDenied になることを確認済み
4-2. IAM の設計（最小権限）
アプリ専用の IAM ユーザー laravel-s3-uploader を作成し、コンソールへのログインは許可していません。権限は次のインラインポリシーのみです。

json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:PutObject", "s3:GetObject", "s3:DeleteObject"],
      "Resource": "arn:aws:s3:::sena-techmeets-images-2026/*"
    },
    {
      "Effect": "Allow",
      "Action": "s3:ListBucket",
      "Resource": "arn:aws:s3:::sena-techmeets-images-2026"
    }
  ]
}
AmazonS3FullAccess などの既製ポリシーは、すべてのバケットの削除や設定変更まで許可してしまうため使用しない
許可しているのは「このバケットの中のファイルのアップロード・取得・削除」と「一覧表示」だけ
4-3. アクセスキーの取り扱い
アクセスキーは src/.env にのみ記載し、.env は .gitignore で Git 管理外にしている
キーを誤ってチャットなどに貼ってしまった場合は、すぐに無効化・削除して新しいキーを発行する（キーのローテーション）
改善案: 本番環境では、アクセスキーを使わずに EC2 に IAM ロールを割り当てる方が安全。キーをファイルに保存する必要がなく、漏えいのリスクそのものがなくなる
5. 費用管理と片付け
AWS アカウントは無料プランで作成し、Budgets でゼロ支出予算（請求アラート）を設定
課題提出後は次のリソースを削除する
RDS（最終スナップショットは作成しない）
EC2（「停止」ではなく「終了」）
S3 バケット（中身を空にしてから削除）
IAM ユーザーのアクセスキー
Elastic IP は今回使用していない
laravel-docker-app
セットアップ手順
1. コンテナを起動する
bash
docker compose up -d
2. Laravelをインストールする
bash
docker compose exec app bash
composer create-project laravel/laravel .
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
exit
3. .envファイルのデータベース設定
.env 内の以下を編集する:

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=secret
4. マイグレーションを実行する
bash
docker compose exec app php artisan migrate
5. 動作確認
Laravelアプリ: http://localhost
phpMyAdmin: http://localhost:8080 （ユーザー名: root / パスワード: secret）
ブログシステムについて
機能
投稿一覧表示(ページネーション付き)
投稿詳細表示
投稿作成(タイトル・内容・カテゴリー)
投稿編集
投稿削除
バリデーション(タイトル・内容・カテゴリーの入力チェック)
Bladeレイアウト継承(共通レイアウトを各ページで使い回し)
テーブル定義
posts テーブル
カラム名	型	説明
id	bigint	主キー(自動採番)
title	varchar	タイトル
content	text	本文
category	varchar	カテゴリー
created_at	timestamp	作成日時
updated_at	timestamp	更新日時
スクリーンショット
投稿一覧
新規投稿フォーム
投稿詳細
Week 9 練習課題2: Fat Controllerのリファクタリング
Week 8で作成した会員制ブログの PostController を、Repository/Serviceパターンと FormRequest を使ってリファクタリングしました。

Before（Week 8）
コントローラーが「バリデーション」「DB操作」「認可」「画面の返却」をすべて担当していました。

php
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
問題点:

バリデーションのルールが store と update に重複している
Post::with('user')->latest()->get() や ->create() など、DB操作がコントローラーに直接書かれている
DBがないとコントローラーの処理を確認できず、テストしにくい
After（Week 9）
php
public function store(PostRequest $request)
{
    $this->postService->createPost($request->user(), $request->validated());

    return redirect()->route('posts.index')->with('success', '投稿しました');
}
コントローラーは「リクエストを受け取り、Serviceに渡し、画面を返す」だけになりました。

責務の分け方
クラス	役割
PostController	リクエストを受け取り、レスポンスを返す
PostRequest	バリデーション（store/updateで共通化）
PostService	ビジネスロジック（ログインユーザーの投稿として作成する、など）
PostRepository	DB操作（Eloquentの処理はここだけに書く）
PostPolicy	認可（自分の投稿だけ編集・削除できる）
変更による効果
重複の解消: バリデーションのルールが PostRequest の1か所にまとまった
変更に強い: 一覧にページネーションを追加したとき、PostRepository と PostService の変更だけで済み、PostController は1行も変更しなかった
テストしやすい: Serviceは Repository をモックに差し替えればDBなしでテストでき、Policyは User と Post を渡すだけでテストできる

Claudeが応答を完了しました
