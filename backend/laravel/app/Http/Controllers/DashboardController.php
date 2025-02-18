<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // 認証済み管理者の取得
        $admin = Auth::guard('admin')->user();

        return view('auth.admin.dashboard.index', [
            'admin' => $admin
        ]);
    }
}
