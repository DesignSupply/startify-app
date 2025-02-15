<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class FrontPageController extends Controller
{

    public function showPage()
    {
        return view('static.frontpage.index');
    }

}
