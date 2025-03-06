<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfileController extends Controller
{
    public function index($id)
    {
        // 管理者ユーザーの取得（CAST関数を使用して厳密な比較）
        $admin = AdminUser::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // 管理者ユーザーが存在しない場合は404エラー
        if (!$admin) {
            abort(404);
        }

        // 現在のログイン管理者ユーザーを取得
        $currentAdmin = Auth::guard('admin')->user();

        // プロフィール画面の表示
        return view('pages.admin.profile.index', [
            'admin' => $admin,
            'isOwn' => $currentAdmin->id === $admin->id
        ]);
    }

    public function redirect()
    {
        $admin = Auth::guard('admin')->user();
        return redirect()->route('admin.profile', ['id' => $admin->id]);
    }

    public function edit($id)
    {
        // 管理者ユーザーの取得
        $admin = AdminUser::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // 管理者ユーザーが存在しない場合は404エラー
        if (!$admin) {
            abort(404);
        }

        // 現在のログイン管理者ユーザーを取得
        $currentAdmin = Auth::guard('admin')->user();

        // 自分以外のプロフィールを編集しようとした場合はプロフィール画面にリダイレクト
        if ($currentAdmin->id !== $admin->id) {
            return redirect()->route('admin.profile', ['id' => $id]);
        }

        // プロフィール編集画面の表示
        return view('pages.admin.profile.edit', [
            'admin' => $admin
        ]);
    }

    public function update(Request $request, $id)
    {
        // 管理者ユーザーの取得
        $admin = AdminUser::whereRaw('CAST(id AS CHAR) = ?', [$id])->first();

        // 管理者ユーザーが存在しない場合は404エラー
        if (!$admin) {
            abort(404);
        }

        // 現在のログイン管理者ユーザーを取得
        $currentAdmin = Auth::guard('admin')->user();

        // 自分以外のプロフィールを更新しようとした場合はプロフィール画面にリダイレクト
        if ($currentAdmin->id !== $admin->id) {
            return redirect()->route('admin.profile', ['id' => $id]);
        }

        // バリデーションルール
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('admin_users')->ignore($admin->id),
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

        // 管理者ユーザー情報の更新
        $admin->name = $validatedData['name'];
        $admin->email = $validatedData['email'];

        // パスワードが入力されている場合のみ更新
        if (!empty($validatedData['password'])) {
            $admin->password = Hash::make($validatedData['password']);
        }

        // 更新を保存
        $admin->save();

        // プロフィール画面にリダイレクトし、フラッシュメッセージを表示
        return redirect()->route('admin.profile', ['id' => $admin->id])
            ->with('success', '管理者プロフィールを更新しました');
    }
}
