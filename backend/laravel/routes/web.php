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

        // 投稿閲覧（一般ユーザー + 管理者）
        Route::prefix('posts')->group(function () {

            // 一覧
            Route::get('/', 'PostController@index')->name('posts.index');

            // 詳細
            Route::get('/{id}', 'PostController@show')
                ->whereNumber('id')
                ->name('posts.show');

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

            // ファイルアップロード
            Route::prefix('files')->group(function () {

                // ファイル一覧
                Route::get('/', 'AdminFilesController@index')->name('admin.files.index');

                // ファイルアップロード
                Route::get('/create', 'AdminFilesController@create')->name('admin.files.create');
                Route::post('/store', 'AdminFilesController@store')->name('admin.files.store');

                // ファイル詳細
                Route::get('/{id}', 'AdminFilesController@show')->name('admin.files.show');

                // ファイル編集・削除
                Route::get('/{id}/edit', 'AdminFilesController@edit')->name('admin.files.edit');
                Route::post('/{id}/update', 'AdminFilesController@update')->name('admin.files.update');
                Route::post('/{id}/delete', 'AdminFilesController@destroy')->name('admin.files.destroy');

                // ファイルダウンロード
                Route::get('/{id}/download', 'AdminFilesController@download')->name('admin.files.download');

            });

            // 一般ユーザー管理
            Route::prefix('users')->group(function () {

                // 一覧
                Route::get('/', 'AdminUsersController@index')
                    ->name('admin.users.index');

                // 詳細
                Route::get('/{id}', 'AdminUsersController@show')
                    ->whereNumber('id')
                    ->name('admin.users.show');

                // 編集
                Route::get('/{id}/edit', 'AdminUsersController@edit')
                    ->whereNumber('id')
                    ->name('admin.users.edit');

                // 更新
                Route::post('/{id}/update', 'AdminUsersController@update')
                    ->whereNumber('id')
                    ->name('admin.users.update');

                // 削除（論理）
                Route::post('/{id}/delete', 'AdminUsersController@destroy')
                    ->whereNumber('id')
                    ->name('admin.users.destroy');

                // 復元
                Route::post('/{id}/restore', 'AdminUsersController@restore')
                    ->whereNumber('id')
                    ->name('admin.users.restore');

            });

            // 投稿管理
            Route::prefix('posts')->group(function () {

                // 作成
                Route::get('/create', 'PostController@create')->name('posts.create');
                Route::post('/store', 'PostController@store')->name('posts.store');

                // 編集
                Route::get('/{id}/edit', 'PostController@edit')
                    ->whereNumber('id')
                    ->name('posts.edit');

                // 更新
                Route::post('/{id}/update', 'PostController@update')
                    ->whereNumber('id')
                    ->name('posts.update');

                // 削除（論理）
                Route::post('/{id}/delete', 'PostController@destroy')
                    ->whereNumber('id')
                    ->name('posts.destroy');

                // 復元
                Route::post('/{id}/restore', 'PostController@restore')
                    ->whereNumber('id')
                    ->name('posts.restore');

            });

            // カテゴリ管理
            Route::prefix('categories')->group(function () {

                // 一覧
                Route::get('/', 'CategoryController@index')->name('categories.index');

                // 作成
                Route::get('/create', 'CategoryController@create')->name('categories.create');
                Route::post('/store', 'CategoryController@store')->name('categories.store');

                // 編集
                Route::get('/{id}/edit', 'CategoryController@edit')
                    ->whereNumber('id')
                    ->name('categories.edit');

                // 更新
                Route::post('/{id}/update', 'CategoryController@update')
                    ->whereNumber('id')
                    ->name('categories.update');

                // 削除（論理）
                Route::post('/{id}/delete', 'CategoryController@destroy')
                    ->whereNumber('id')
                    ->name('categories.destroy');

                // 復元
                Route::post('/{id}/restore', 'CategoryController@restore')
                    ->whereNumber('id')
                    ->name('categories.restore');

            });

            // タグ管理
            Route::prefix('tags')->group(function () {

                // 一覧
                Route::get('/', 'TagController@index')->name('tags.index');

                // 作成
                Route::get('/create', 'TagController@create')->name('tags.create');
                Route::post('/store', 'TagController@store')->name('tags.store');

                // 編集
                Route::get('/{id}/edit', 'TagController@edit')
                    ->whereNumber('id')
                    ->name('tags.edit');

                // 更新
                Route::post('/{id}/update', 'TagController@update')
                    ->whereNumber('id')
                    ->name('tags.update');

                // 削除（論理）
                Route::post('/{id}/delete', 'TagController@destroy')
                    ->whereNumber('id')
                    ->name('tags.destroy');

                // 復元
                Route::post('/{id}/restore', 'TagController@restore')
                    ->whereNumber('id')
                    ->name('tags.restore');

            });

        });
    });
});
