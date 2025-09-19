<?php

namespace App\Controllers;

/** Home landing untuk user setelah login (default). */
class Home extends BaseController
{
    public function index()
    {
        // Cek role atau langsung load view untuk user
        return view('user/home');
    }
}


