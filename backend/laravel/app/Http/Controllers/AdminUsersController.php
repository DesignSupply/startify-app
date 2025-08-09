<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return view('pages.admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('pages.admin.users.show', [
            'user' => $user,
        ]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('pages.admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $messages = [
            'name.required' => '名前は必須項目です。',
            'name.max' => '名前は255文字以内で入力してください。',
            'email.required' => 'メールアドレスは必須項目です。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
        ];

        $validated = $request->validate($rules, $messages);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('status', 'ユーザー情報を更新しました。');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        DB::transaction(function () use ($user) {
            // 論理削除（冪等）
            if (!$user->is_deleted) {
                $user->is_deleted = true;
                $user->deleted_at = now();
                $user->save();
            }

            // セッションの強制終了
            DB::table('sessions')->where('user_id', $user->id)->delete();
        });

        \Log::info('Admin deleted user (logical)', [
            'target_user_id' => $user->id,
            'by_admin_id' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'ユーザーを削除しました。');
    }

    public function restore($id)
    {
        $user = User::findOrFail($id);

        if ($user->is_deleted) {
            $user->is_deleted = false;
            $user->deleted_at = null;
            $user->save();
        }

        \Log::info('Admin restored user', [
            'target_user_id' => $user->id,
            'by_admin_id' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'ユーザーを復元しました。');
    }
}


