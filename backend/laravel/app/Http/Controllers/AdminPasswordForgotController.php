<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\AdminUser;
use App\Notifications\AdminPasswordResetNotification;

class AdminPasswordForgotController extends Controller
{
    public function index()
    {
        return view('pages.admin.password-forgot.index');
    }

    public function sendMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレスの形式で入力してください',
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors([
                'email' => '登録されていないメールアドレスです',
            ]);
        }

        try {
            // パスワードリセットトークンの生成
            $token = Password::broker('admins')->createToken($admin);

            // パスワードリセット通知の送信
            $admin->notify(new AdminPasswordResetNotification($token));

            \Log::info('Admin password reset notification sent', [
                'email' => $admin->email,
                'token' => $token
            ]);

            return back()->with('status', '登録されているメールアドレスにパスワードリセット用のメールを送信しました');
        } catch (\Exception $e) {
            \Log::error('Failed to send admin password reset notification', [
                'error' => $e->getMessage(),
                'email' => $admin->email
            ]);

            return back()->withErrors([
                'email' => 'メールの送信に失敗しました'
            ]);
        }
    }
}
