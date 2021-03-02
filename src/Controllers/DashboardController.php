<?php

namespace Mariojgt\Magnifier\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('magnifier::content.dashboard.index');
    }
}
