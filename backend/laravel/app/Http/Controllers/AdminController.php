<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;

class AdminController extends Controller
{
    public function index()
    {
        return view('static.admin.index');
    }

    public function signIn(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // メールアドレスの確認
        $admin = AdminUser::where('email', $credentials['email'])->first();
        if (!$admin) {
            return redirect()->route('admin')->withErrors([
                'email' => 'このメールアドレスは登録されていません。',
            ]);
        }

        // パスワードの確認
        if (!Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin')->withErrors([
                'password' => 'パスワードが正しくありません。',
            ]);
        }

        // 認証成功
        $request->session()->regenerate();
        $request->session()->flash('status', 'ログインに成功しました！');
        return redirect()->route('dashboard');
    }

    public function signOut(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('status', 'ログアウトしました。');
        return redirect()->route('admin');
    }
}
