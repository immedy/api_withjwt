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
        $validasiData = $request->only(['username', 'password']);
        if (!$token = Auth()->attempt($validasiData)) {
            return response()->json(['error' => 'Unauthorized'], 400);
        }
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => auth()->user()->id,
                'pegawai_id' => auth()->user()->pegawai_id,
                'username' => auth()->user()->username,
                'device_id' => auth()->user()->device_id,
                'created_at' => auth()->user()->created_at,
                'updated_at' => auth()->user()->updated_at,
                'token' => $token, // Memasukkan token JWT ke dalam data pengguna
            ],
            'message' => "Authentikasi sukses"
        ]);
    }

    public function getUser(Request $request)
    {
        $user = auth()->user(); // Mendapatkan objek user yang terautentikasi

        $userData = [
            'id' => $user->id,
            'pegawai_id' => $user->pegawai_id,
            'nama_lengkap' => $user->Pegawai->nama,
            'nip' => $user->Pegawai->nip,
        ];

        return response()->json($userData);
    }
}
