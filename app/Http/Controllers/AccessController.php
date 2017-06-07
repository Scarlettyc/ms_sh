<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    /**
     * Show the application dashboard.test
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('home');
    }
}
