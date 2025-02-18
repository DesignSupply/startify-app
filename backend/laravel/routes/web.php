<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {

    // フロントページ
    Route::get('/', 'FrontPageController@index')->name('frontpage');

    // ログイン
    Route::get('/signin', 'SignInController@index')->name('signin');
    Route::post('/signin', 'SignInController@signIn')->name('signin.post');

    // 認証ルーティング
    Route::middleware('auth')->group(function () {

        // ホーム
        Route::get('/home', 'HomeController@index')->name('home');

        // ログアウト
        Route::post('/signout', 'SignOutController@signOut')->name('signout.post');

    });

    // 管理者ログインページ
    Route::get('/admin', 'AdminController@index')->name('admin');
    Route::post('/admin', 'AdminController@signIn')->name('admin.signin.post');

    // 管理者認証ルーティング
    Route::middleware('auth:admin')->group(function () {

        // 管理者ダッシュボード
        Route::get('/admin/dashboard', 'DashboardController@index')->name('dashboard');

        // 管理者ログアウト
        Route::post('/admin/signout', 'AdminController@signOut')->name('admin.signout.post');

    });

});
