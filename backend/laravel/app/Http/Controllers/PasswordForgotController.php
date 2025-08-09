<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Notifications\PasswordResetNotification;

class PasswordForgotController extends Controller
{
    public function index()
    {
        return view('pages.password-forgot.index');
    }

    public function sendMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレスの形式で入力してください',
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

        try {
            // パスワードリセットトークンの生成
            $token = Password::createToken($user);

            // パスワードリセット通知の送信
            $user->notify(new PasswordResetNotification($token));

            \Log::info('Password reset notification sent', [
                'email' => $user->email,
                'token' => $token
            ]);

            return back()->with('status', '登録されているメールアドレスにパスワードリセット用のメールを送信しました');
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset notification', [
                'error' => $e->getMessage(),
                'email' => $user->email
            ]);

            return back()->withErrors([
                'email' => 'メールの送信に失敗しました'
            ]);
        }
    }
}
