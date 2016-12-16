<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
    	$name = $request->input('name');
    	$email = $request->input('email');
    	$password = $request->input('password');
    }

    /**
     * Authenticate the user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $request) {
    	$email = $request->input('email');
    	$password = $request->input('password');
    }
}
