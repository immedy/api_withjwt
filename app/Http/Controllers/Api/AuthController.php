<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\absensi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


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

        $user = Auth::user();
        $device_id = $request->device_id;

        // Validasi device_id
        $userWithDeviceId = User::where('device_id', $device_id)->first();
        if ($userWithDeviceId && $userWithDeviceId->id != $user->id) {
            return response()->json(['error' => 'Device not allowed'], 403);
        }

        // Simpan device_id ke dalam user
        if (empty($user->device_id)) {
            $user->device_id = $device_id;
            $user->save();
        } else {
            // Periksa device_id
            if ($user->device_id != $device_id) {
                return response()->json(['error' => 'Device not allowed'], 403);
            }
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
            'pegawai_id' =>  $user->pegawai_id,
            'nama_lengkap' => $user->Pegawai->gelar_depan . '. ' . $user->Pegawai->nama . ', ' . $user->Pegawai->gelar_belakang,
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
            $tanggalabsen = now()->format('Y-m-d');
            $jamabsen = now()->format('H:i:s');
            $absensi = new Absensi();
            $absensi->pegawai_id = $pegawaiId;
            $absensi->absensi_tanggal = $tanggalabsen;
            $absensi->absensi_waktu = $jamabsen;
            $absensi->jenis_absen = $jenisAbsen;
            $absensi->status_absen = 0;
            $absensi->save();

            return response()->json([
                'success' => true,
                'data' => $absensi,
                'message' => 'Absensi Masuk berhasil'
            ], 200);
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
            $tanggalabsen = now()->format('Y-m-d');
            $jamabsen = now()->format('H:i:s');
            $absensi = new Absensi();
            $absensi->pegawai_id = $pegawaiId;
            $absensi->absensi_tanggal = $tanggalabsen;
            $absensi->absensi_waktu = $jamabsen;
            $absensi->jenis_absen = $jenisAbsen;
            $absensi->status_absen = 1;
            $absensi->save();

            return response()->json([
                'success' => true,
                'data' => $absensi,
                'message' => 'Absensi Pulang Berhasil'
            ], 200);
        } else {
            return response()->json(['message' => 'QR code tidak valid'], 400);
        }
    }
    public function auth()
    {
        return $this->user()->can('update', $this->user());
    }

    public function rules()
    {
        $user = $this->user()->id;
        return [
            'device_id' => 'required|string|unique:users,device_id,' . $user,
        ];
    }
    public function addDeviceId(Request $request)
    {

        $user = $request->user();

        $validated = $request->validate([
            'device_id' => 'required|string|unique:users,device_id,' . $user->id,
        ], [
            'unique_device_id_check' => 'This device already exists for the user.',
        ]);

        $user->device_id = $validated['device_id'];
        $user->save();

        return response()->json([
            'success' => true,
            'device_id' => $user->device_id,
            'message' => 'Device ID updated successfully',
        ], 200);
    }

    public function listEmployee()
    {
        $user = Auth()->user();
        $employee = absensi::where('pegawai_id', $user->pegawai_id)->get();
        return response()->json($employee);

    }
}
