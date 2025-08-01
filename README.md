# coachtechフリマ （FleaMarket）

## 環境構築

### Docker ビルド
- 1.git clone git@github.com:okumurachie/Flea-market.git
- 2.docker-compose up -d --build

### laravel 環境構築
- 1.docker-compose exec php bash
- 2.composer install
- 3.cp .env.example .env(.env.example ファイルから.env を作成し、環境変数を変更)


        DB_HOST=mysql
        DB_DATABASE=laravel_db
        DB_USERNAME=laravel_user
        DB_PASSWORD=laravel_pass

        MAIL_MAILER=smtp
        MAIL_HOST=mailhog
        MAIL_PORT=1025
        MAIL_FROM_ADDRESS=test@example.com

        STRIPE_PUBLIC_KEY=pk_test_*************
        STRIPE_SECRET_KEY=sk_test_*************

- 4.php artisan key:generate
- 5.php artisan migrate
- 6.php artisan db:seed

## 使用技術
- PHP 8.4.8
- Laravel 10.48.29
- MySQL 8.0
- nginx 1.21.1

---
## 購入データの保存（コンビニ支払いの場合）
コンビニ支払いはカード決済と異なり、即時に支払いが確定しないため、StripeのWebhookを使用し、支払い完了後に購入データを保存する仕組みを構築しました。StripeWebhookController を作成し、payment_intent.succeeded イベント受信時に購入データを保存する処理を実装しました。

### テスト用イベント送信
以下のコマンドを使用し、Stripeのイベントをローカルサーバーへ転送します。
```bash
        stripe listen --forward-to http://localhost/webhook/stripe
```

以下のコマンドでpayment_intent.succeededイベントを手動でトリガーし、/webhook/stripeに送信します。
```bash
        stripe trigger payment_intent.succeeded \
        --add payment_intent:metadata.item_id=4 \    購入する商品のID
        --add payment_intent:metadata.user_id=6      購入者ID
```
※ item_id と user_id は実際の購入処理からは自動で入らないため、テスト時には手動で指定します。

## ER 図

![ER図](./index.png)

## 開発環境

- 商品一覧画面（トップ画面）：http://localhost/
- 会員登録：http://localhost/register
- phpMyAdmin:http://localhost:8080/
# Flea-market
