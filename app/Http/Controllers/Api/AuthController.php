<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    public function login(Request $request)
    {
        $validasiData = $request->only(['email','password']);
        if (! $token = auth()->attempt($validasiData)){
            return response()->json(['error' => 'Unauthorized'], 400);
        }
        return $this->respondWithToken($token);
    }
    
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' =>true,
            'data' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'email_verified_at' => auth()->user()->email_verified_at,
                'created_at' => auth()->user()->created_at,
                'updated_at' => auth()->user()->updated_at,
                'access_token' => $token, // Memasukkan token JWT ke dalam data pengguna
            ],
            'message'=> "Authentikasi sukses"
        ]);

    }
    
    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }
}
