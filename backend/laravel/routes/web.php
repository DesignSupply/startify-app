<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {

    // フロントページ
    Route::get('/', 'FrontPageController@index')->name('frontpage');

    // ログイン
    Route::get('/signin', 'SignInController@index')->name('signin');
    Route::post('/signin/auth', 'SignInController@signIn')->name('signin.auth');

    // コンタクトフォーム
    Route::prefix('contact')->group(function () {

        // 入力画面
        Route::get('/', 'ContactController@index')->name('contact');
        Route::post('/form', 'ContactController@form')->name('contact.form');

        // 確認画面
        Route::get('/confirm', 'ContactController@confirm')->name('contact.confirm');
        Route::post('/send', 'ContactController@send')->name('contact.send');

        // 完了画面
        Route::get('/thanks', 'ContactController@thanks')->name('contact.thanks');

    });

    // 未認証ルーティング
    Route::middleware('guest')->group(function () {

        // パスワードリセット（メールアドレス確認）
        Route::get('/password-forgot', 'PasswordForgotController@index')->name('password-forgot');
        Route::post('/password-forgot/request', 'PasswordForgotController@sendMail')->name('password-forgot.request');

        // パスワードリセット（パスワード再設定）
        Route::get('/password-reset/{token}', 'PasswordResetController@index')->name('password-reset');
        Route::post('/password-reset/reset', 'PasswordResetController@reset')->name('password-reset.reset');

        // 新規ユーザー登録
        Route::prefix('signup')->group(function () {

            // メールアドレス確認
            Route::get('/', 'SignUpController@index')->name('signup');
            Route::post('/request', 'SignUpController@verifyEmail')->name('signup.request');

            // メール送信完了・確認待ち
            Route::get('/pending', 'SignUpController@pending')->name('signup.pending');

            // メールアドレス検証（トークン検証）
            Route::get('/verify/{token}', 'SignUpController@verifyToken')->name('signup.verify');

            // 新規登録フォーム
            Route::get('/register', 'SignUpController@form')->name('signup.register');
            Route::post('/register/store', 'SignUpController@register')->name('signup.register.store');

            // 新規登録完了
            Route::get('/complete', 'SignUpController@complete')->name('signup.complete');

        });
    });

    // 認証ルーティング
    Route::middleware('auth')->group(function () {

        // ホーム
        Route::get('/home', 'HomeController@index')->name('home');

        // ユーザープロフィール
        Route::prefix('profile')->group(function () {

            // プロフィール表示
            Route::get('/', 'ProfileController@redirect')->name('profile.redirect');
            Route::get('/{id}', 'ProfileController@index')->name('profile');

            // プロフィール編集・更新
            Route::get('/{id}/edit', 'ProfileController@edit')->name('profile.edit');
            Route::post('/{id}/update', 'ProfileController@update')->name('profile.update');

        });

        // ログアウト
        Route::post('/signout', 'SignOutController@signOut')->name('signout');

    });

    // 管理者ルーティング
    Route::prefix('admin')->group(function () {

        // 管理者ログインページ
        Route::get('/', 'AdminController@index')->name('admin');
        Route::post('/signin', 'AdminController@signIn')->name('admin.signin');

        // 管理者パスワードリセット（メールアドレス確認）
        Route::get('/password-forgot', 'AdminPasswordForgotController@index')->name('admin.password-forgot');
        Route::post('/password-forgot/request', 'AdminPasswordForgotController@sendMail')->name('admin.password-forgot.request');

        // 管理者パスワードリセット（パスワード再設定）
        Route::get('/password-reset/{token}', 'AdminPasswordResetController@index')->name('admin.password-reset');
        Route::post('/password-reset/reset', 'AdminPasswordResetController@reset')->name('admin.password-reset.reset');

    });

    // 管理者認証ルーティング
    Route::middleware('auth:admin')->group(function () {
        Route::prefix('admin')->group(function () {

            // 管理者ダッシュボード
            Route::get('/dashboard', 'DashboardController@index')->name('admin.dashboard');

            // 管理者プロフィール
            Route::prefix('profile')->group(function () {

                // プロフィール表示
                Route::get('/', 'AdminProfileController@redirect')->name('admin.profile.redirect');
                Route::get('/{id}', 'AdminProfileController@index')->name('admin.profile');

                // プロフィール編集・更新
                Route::get('/{id}/edit', 'AdminProfileController@edit')->name('admin.profile.edit');
                Route::post('/{id}/update', 'AdminProfileController@update')->name('admin.profile.update');

            });

            // 管理者ログアウト
            Route::post('/signout', 'AdminController@signOut')->name('admin.signout');

        });
    });
});
