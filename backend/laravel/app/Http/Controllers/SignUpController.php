<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\SignUpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SignUpController extends Controller
{
    public function index()
    {
        return view('pages.signup.verify');
    }

    public function verifyEmail(Request $request)
    {
        $messages = [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
        ];

        $request->validate([
            'email' => ['required', 'email', 'unique:users'],
        ], $messages);

        $token = Str::random(60);
        $user = new User();
        $user->email = $request->email;
        $user->notify(new SignUpNotification($token));

        session(['signup_email' => $request->email]);
        session(['signup_token' => $token]);

        return redirect()->route('signup.pending');
    }

    public function pending()
    {
        if (!session('signup_email')) {
            return redirect()->route('signup');
        }

        return view('pages.signup.pending');
    }

    public function verifyToken(Request $request, $token)
    {
        if ($token !== session('signup_token')) {
            return redirect()->route('signup')
                ->withErrors(['email' => 'トークンが無効です。']);
        }

        if ($request->email !== session('signup_email')) {
            return redirect()->route('signup')
                ->withErrors(['email' => 'メールアドレスが一致しません。']);
        }

        return redirect()->route('signup.register');
    }

    public function form()
    {
        if (!session('signup_email')) {
            return redirect()->route('signup');
        }

        return view('pages.signup.register', [
            'email' => session('signup_email')
        ]);
    }

    public function register(Request $request)
    {
        if (!session('signup_email')) {
            return redirect()->route('signup');
        }

        $messages = [
            'name.required' => 'お名前を入力してください。',
            'name.string' => '有効な文字列を入力してください。',
            'name.max' => 'お名前は255文字以内で入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.string' => '有効な文字列を入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードが確認用と一致しません。',
        ];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $messages);

        $user = User::create([
            'name' => $request->name,
            'email' => session('signup_email'),
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        session()->forget(['signup_email', 'signup_token']);

        return redirect()->route('signup.complete');
    }

    public function complete()
    {
        return view('pages.signup.complete');
    }
}
