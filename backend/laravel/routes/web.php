<?php

use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(function () {

    // フロントページ
    Route::get('/', 'FrontPageController@showPage')->name('frontpage');

});
