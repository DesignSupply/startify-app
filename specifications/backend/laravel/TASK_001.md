---
title: バックエンド実装タスクリスト（Laravel）:API用ルーティングの追加
id: laravel_task_001
version: 1.0.0
last_updated: 2025-03-03
purpose: AI支援による開発環境構築のための仕様書
target_readers: ウェブエンジニア（バックエンド、フロントエンド）、UIデザイナー
---

# バックエンド実装タスクリスト（Laravel）:API用ルーティングの追加

---

## 1. API用ルーティングの追加

API用ルーティングの追加を行い、`backend/laravel/app/bootstrap/app.php` を下記のように変更します。

```bash
cd /server
docker compose exec app bash -c "cd /var/www/html/laravel && php artisan install:api"
```

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

```

設定後はキャッシュをクリアして変更を反映させます。

```bash
cd /server
make laravel-cache-clear
make laravel-config-clear
```
