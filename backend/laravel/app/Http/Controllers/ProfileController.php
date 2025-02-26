<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index($id)
    {
        // ユーザーの取得（CAST関数を使用して厳密な比較）
        $user = User::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // ユーザーが存在しない場合は404エラー
        if (!$user) {
            abort(404);
        }

        // 現在のログインユーザーを取得
        $currentUser = Auth::user();

        // プロフィール画面の表示
        return view('pages.profile.index', [
            'user' => $user,
            'isOwn' => $currentUser->id === $user->id
        ]);
    }

    public function redirect()
    {
        $user = Auth::user();
        return redirect()->route('profile', ['id' => $user->id]);
    }
}
