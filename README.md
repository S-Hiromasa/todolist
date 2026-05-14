# Laravel ToDo List

Laravel で作ったシンプルな ToDo リストです。追加、編集、削除、完了切り替え、期限入力ができます。

## セットアップ

この環境には PHP と Composer が入っていなかったため、ここでは実行確認まで行っていません。PHP 8.2 以上と Composer が使える環境で次を実行してください。

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

ブラウザで `http://localhost:8000` を開くと ToDo リストが表示されます。

## テスト

```bash
php artisan test
```
