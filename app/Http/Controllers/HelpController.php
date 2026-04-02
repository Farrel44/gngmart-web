<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    /**
     * Display the help page about how to shop
     */
    public function index()
    {
        return view('help.index');
    }
}
