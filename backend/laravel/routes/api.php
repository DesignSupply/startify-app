<?php

use Illuminate\Support\Facades\Route;

// MPA側に合わせた記法（名前空間 + 文字列コントローラー指定）
Route::namespace('App\Http\Controllers\Api')->group(function () {

    // /v1/auth （LaravelのwithRoutingで /api が自動付与されるためここでは付けない）
    Route::prefix('v1')->group(function () {
        Route::prefix('auth')->group(function () {

            // ログイン
            Route::post('/login', 'AuthController@login')->middleware(['throttle:login','api.guard'])->name('api.v1.auth.login');

            // リフレッシュトークン再発行（credentials: include）
            Route::post('/refresh', 'AuthController@refresh')->middleware(['throttle:refresh','api.guard'])->name('api.v1.auth.refresh');

            // ログアウト（credentials: include）
            Route::post('/logout', 'AuthController@logout')->middleware('api.guard')->name('api.v1.auth.logout');

            // JWT検証（jwtミドルウェア適用）
            Route::get('/me', 'AuthController@me')->middleware('jwt')->name('api.v1.auth.me');
        });
    });
});
