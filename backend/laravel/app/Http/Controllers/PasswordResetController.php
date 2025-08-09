<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.password-reset.index', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードが確認用の入力値と一致しません',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレスの形式で入力してください',
            'token.required' => '不正なアクセスです',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => '登録されていないメールアドレスです',
            ]);
        }

        // 削除ユーザーの場合
        if ($user->is_deleted) {
            return back()->withErrors([
                'email' => '削除済みアカウントのパスワードリセットはできません',
            ]);
        }

        // トークンの検証
        if (!Password::tokenExists($user, $request->token)) {
            return back()->withErrors([
                'email' => 'トークンが無効です',
            ]);
        }

        // パスワードの更新
        $user->password = Hash::make($request->password);
        $user->save();

        // トークンの削除
        Password::deleteToken($user);

        return redirect()->route('signin')
            ->with('status', 'パスワードを再設定しました');
    }
}
