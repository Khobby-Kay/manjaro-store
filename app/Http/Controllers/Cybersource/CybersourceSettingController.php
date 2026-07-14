<?php

namespace App\Http\Controllers\Cybersource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CybersourceSettingController extends Controller
{
    // Empty controller to fix route loading issue
    public function index()
    {
        return response()->json(['message' => 'Cybersource controller placeholder']);
    }
}
