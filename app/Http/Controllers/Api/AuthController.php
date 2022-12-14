<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Api;
use App\Helpers\ApiValidate;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        $credentials = ApiValidate::login($request, User::class);

        if (Auth::attempt($credentials)) {
            return Api::setResponse('user', Auth::user()->withToken());
        } else {
            return Api::setError('Invalid credentials');
        }
    }
}
