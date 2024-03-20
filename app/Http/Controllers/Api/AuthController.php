<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\absensi;
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
            'jenis_absen' => $user->Pegawai->jenis_absen,
        ];

        return response()->json($userData);
    }
    public function scanQRcodeAbsenMasuk(Request $request)
    {

        $user = auth()->user();
        $pegawaiId = $user->pegawai_id;
        $jenisAbsen = $user->Pegawai->jenis_absen;
        $qrCode = $request->input('qrcode');
        if ($qrCode === 'qrcodeabsenmasuk') {
            $waktuAbsen = now();
            $absensi = new Absensi();
            $absensi->pegawai_id = $pegawaiId;
            $absensi->absensi = $waktuAbsen;
            $absensi->jenis_absen = $jenisAbsen;
            $absensi->status_absen = 0;
            $absensi->save();

            return response()->json(['message' => 'Absensi Masuk berhasil'], 200);
        } else {
            return response()->json(['message' => 'QR code tidak valid'], 400);
        }
    }

    public function scanQRcodeAbsenPulang(Request $request)
    {
        $user = auth()->user();
        $pegawaiId = $user->pegawai_id;
        $jenisAbsen = $user->Pegawai->jenis_absen;
        $qrCode = $request->input('qrcode');
        if ($qrCode === 'qrcodeabsenpulang') {
            $waktuAbsen = now();
            $absensi = new Absensi();
            $absensi->pegawai_id = $pegawaiId;
            $absensi->absensi = $waktuAbsen;
            $absensi->jenis_absen = $jenisAbsen;
            $absensi->status_absen = 1;
            $absensi->save();

            return response()->json(['message' => 'Absensi Pulang Berhasil'], 200);
        } else {
            return response()->json(['message' => 'QR code tidak valid'], 400);
        }

    }
}
