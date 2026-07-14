<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyFatoorahController extends Controller
{
    // Empty controller to fix route loading issue
    public function index()
    {
        return response()->json(['message' => 'MyFatoorah controller placeholder']);
    }
}

