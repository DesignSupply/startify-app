<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

    public function edit($id)
    {
        // ユーザーの取得
        $user = User::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // ユーザーが存在しない場合は404エラー
        if (!$user) {
            abort(404);
        }

        // 現在のログインユーザーを取得
        $currentUser = Auth::user();

        // 自分以外のプロフィールを編集しようとした場合はプロフィール画面にリダイレクト
        if ($currentUser->id !== $user->id) {
            return redirect()->route('profile', ['id' => $id]);
        }

        // プロフィール編集画面の表示
        return view('pages.profile.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        // ユーザーの取得
        $user = User::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // ユーザーが存在しない場合は404エラー
        if (!$user) {
            abort(404);
        }

        // 現在のログインユーザーを取得
        $currentUser = Auth::user();

        // 自分以外のプロフィールを更新しようとした場合はプロフィール画面にリダイレクト
        if ($currentUser->id !== $user->id) {
            return redirect()->route('profile', ['id' => $id]);
        }

        // バリデーションルール
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // カスタムエラーメッセージ
        $messages = [
            'name.required' => '名前は必須項目です。',
            'name.max' => '名前は255文字以内で入力してください。',
            'email.required' => 'メールアドレスは必須項目です。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
        ];

        // バリデーション実行
        $validatedData = $request->validate($rules, $messages);

        // ユーザー情報の更新
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        // パスワードが入力されている場合のみ更新
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // 更新を保存
        $user->save();

        // プロフィール画面にリダイレクトし、フラッシュメッセージを表示
        return redirect()->route('profile', ['id' => $user->id])
            ->with('success', 'プロフィールを更新しました');
    }
}
