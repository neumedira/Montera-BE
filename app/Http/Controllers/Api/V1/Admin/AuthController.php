<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required', // Bisa diisi email atau username
            'password' => 'required'
        ]);

        // Cari user berdasarkan email ATAU username
        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        // Cek kecocokan password_hash
        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return $this->errorResponse('Kredensial tidak valid', null, 401);
        }

        // Generate Token Sanctum
        $token = $user->createToken('admin_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logout berhasil');
    }
}
