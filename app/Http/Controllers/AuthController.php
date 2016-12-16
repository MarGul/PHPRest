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
    	
    	$this->validate($request, [
    		'name' => 'required',
    		'email' => 'required|email',
    		'password' => 'required|min:5'
    	]);

    	$name = $request->input('name');
    	$email = $request->input('email');
    	$password = $request->input('password');

    	$user = [
    		'name' => $name
    	];

    	$response = [
    		'message' => 'User successfully created.',
    		'user' => $user,
    		'sign_in' => [
    			'href' => 'api/v1/user/signin',
    			'method' => 'POST',
    			'params' => 'email, password'
    		]
    	];

    	return response()->json($response, 201);
    }

    /**
     * Authenticate the user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $request) {

    	$this->validate($request, [
    		'email' => 'required|email',
    		'password' => 'required'
    	]);

    	$email = $request->input('email');
    	$password = $request->input('password');
    }
}
