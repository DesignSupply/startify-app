<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SignOutController extends Controller
{
    /**
     * ログアウト処理
     */
    public function signOut(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('status', 'ログアウトしました。');
        return redirect()->route('signin');
    }

}
