<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Cek role atau langsung load view untuk user
        return view('user/home');
    }
}


