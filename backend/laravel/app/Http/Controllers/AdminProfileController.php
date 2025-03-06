<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;

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
}
