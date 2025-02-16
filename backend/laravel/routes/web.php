<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {

    // フロントページ
    Route::get('/', 'FrontPageController@showPage')->name('frontpage');

    // ログイン
    Route::get('/signin', 'SignInController@showPage')->name('signin');
    Route::post('/signin', 'SignInController@signIn')->name('signin.post');

    // 認証ルーティング
    Route::middleware('auth')->group(function () {

        // ホーム
        Route::get('/home', 'HomeController@showPage')->name('home');

        // ログアウト
        Route::post('/signout', 'SignOutController@signOut')->name('signout');

    });

});
