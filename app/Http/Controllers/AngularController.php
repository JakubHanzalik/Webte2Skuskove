<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class AngularController extends Controller
{
    public function index()
    {
        return view('angular');
    }
}
