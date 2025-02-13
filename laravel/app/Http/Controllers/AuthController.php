<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator:: make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);



        if ($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    public function login(Request $request){
        $credentials = $request->only('email','password');

        if (!$token = JWTAuth::attempt($credentials)){
            return response()->json(['error' => 'Sin autorizacion'], 401);
        }

        return response()->json(compact('token'));
    }

    public function me(){
        return response()->json(JWTAuth::user());
    }

    public function logout(){

        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Sesion cerrada con exito']);
    }

    //
}
