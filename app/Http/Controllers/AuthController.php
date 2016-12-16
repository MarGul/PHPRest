<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function store(Request $request) {
    	return "Store method works";
    }

    public function signin(Request $request) {
    	return "Sign in method works";
    }
}
