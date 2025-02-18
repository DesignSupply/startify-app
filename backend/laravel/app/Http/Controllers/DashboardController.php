<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        return view('auth.admin.dashboard.index', [
            'admin' => $admin
        ]);
    }
}
