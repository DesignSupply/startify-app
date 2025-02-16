<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function showPage()
    {
        return view('auth.home.index');
    }

}
