<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SignInController extends Controller
{

    public function showPage()
    {
        return view('auth.signin.index');
    }

    public function signIn(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // メールアドレスの確認
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return redirect()->route('signin')->withErrors([
                'email' => 'このメールアドレスは登録されていません。',
            ]);
        }

        // パスワードの確認
        if (!Auth::attempt($credentials)) {
            return redirect()->route('signin')->withErrors([
                'password' => 'パスワードが正しくありません。',
            ]);
        }

        // 認証成功
        $request->session()->flash('status', 'ログインに成功しました！');
        return redirect()->intended('/home');
    }

}
