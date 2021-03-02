<?php

namespace Mariojgt\Magnifier\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeContoller extends Controller
{
    public function index()
    {
        return view('magnifier::content.index');
    }
}
