<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ゲストリダイレクト先の設定
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('admin*') ? '/admin' : '/signin';
        });

        // ルートミドルウェアのエイリアス登録（jwt）
        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtAuthenticate::class,
        ]);

    })
    ->withSchedule(function (Schedule $schedule) {
        // 毎日 00:00 にトークンクリーンアップを実行
        $schedule->command('tokens:prune')->dailyAt('00:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
