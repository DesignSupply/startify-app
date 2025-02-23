<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {

    // フロントページ
    Route::get('/', 'FrontPageController@index')->name('frontpage');

    // ログイン
    Route::get('/signin', 'SignInController@index')->name('signin');
    Route::post('/signin', 'SignInController@signIn')->name('signin.post');

    // 未認証ルーティング
    Route::middleware('guest')->group(function () {

        // パスワードリセット（メールアドレス確認）
        Route::get('/password-forgot', 'PasswordForgotController@index')->name('password-forgot');
        Route::post('/password-forgot', 'PasswordForgotController@sendMail')->name('password-forgot.post');

        // パスワードリセット（パスワード再設定）
        Route::get('/password-reset/{token}', 'PasswordResetController@index')->name('password-reset');
        Route::post('/password-reset', 'PasswordResetController@reset')->name('password-reset.post');

        // 新規ユーザー登録
        Route::prefix('signup')->group(function () {

            // メールアドレス確認
            Route::get('/', 'SignUpController@index')->name('signup');
            Route::post('/', 'SignUpController@verifyEmail')->name('signup.post');

            // メール送信完了・確認待ち
            Route::get('/pending', 'SignUpController@pending')->name('signup.pending');

            // メールアドレス検証（トークン検証）
            Route::get('/verify/{token}', 'SignUpController@verifyToken')->name('signup.verify');

            // 新規登録フォーム
            Route::get('/register', 'SignUpController@form')->name('signup.register');
            Route::post('/register', 'SignUpController@register')->name('signup.register.post');

            // 新規登録完了
            Route::get('/complete', 'SignUpController@complete')->name('signup.complete');

        });
    });

    // 認証ルーティング
    Route::middleware('auth')->group(function () {

        // ホーム
        Route::get('/home', 'HomeController@index')->name('home');

        // ログアウト
        Route::post('/signout', 'SignOutController@signOut')->name('signout.post');

    });

    // 管理者ルーティング
    Route::prefix('admin')->group(function () {

        // 管理者ログインページ
        Route::get('/', 'AdminController@index')->name('admin');
        Route::post('/', 'AdminController@signIn')->name('admin.signin.post');

        // 管理者パスワードリセット（メールアドレス確認）
        Route::get('/password-forgot', 'AdminPasswordForgotController@index')->name('admin.password-forgot');
        Route::post('/password-forgot', 'AdminPasswordForgotController@sendMail')->name('admin.password-forgot.post');

        // 管理者パスワードリセット（パスワード再設定）
        Route::get('/password-reset/{token}', 'AdminPasswordResetController@index')->name('admin.password-reset');
        Route::post('/password-reset', 'AdminPasswordResetController@reset')->name('admin.password-reset.post');

    });

    // 管理者認証ルーティング
    Route::middleware('auth:admin')->group(function () {
        Route::prefix('admin')->group(function () {

            // 管理者ダッシュボード
            Route::get('/dashboard', 'DashboardController@index')->name('admin.dashboard');

            // 管理者ログアウト
            Route::post('/signout', 'AdminController@signOut')->name('admin.signout.post');

        });
    });

});
