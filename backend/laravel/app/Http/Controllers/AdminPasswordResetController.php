<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

class AdminPasswordResetController extends Controller
{
    public function index(Request $request)
    {
        return view('static.admin.password-reset.index', [
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

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors([
                'email' => '登録されていないメールアドレスです',
            ]);
        }

        // トークンの検証
        if (!Password::broker('admins')->tokenExists($admin, $request->token)) {
            return back()->withErrors([
                'email' => 'トークンが無効です',
            ]);
        }

        // パスワードの更新
        $admin->password = Hash::make($request->password);
        $admin->save();

        // トークンの削除
        Password::broker('admins')->deleteToken($admin);

        return redirect()->route('admin')
            ->with('status', 'パスワードを再設定しました');
    }
}
