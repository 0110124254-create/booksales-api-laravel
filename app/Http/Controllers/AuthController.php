<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'data' => null, 'errors' => $validator->errors()], 422);
        }

        $credentials = $validator->validated();
        $user = User::query()->where('email', strtolower(trim($credentials['email'])))->first();
        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'The provided credentials are incorrect.', 'data' => null], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully',
            'data' => [
                'user' => $user->only(['id', 'name', 'username', 'email', 'is_admin']),
                'token' => $user->createToken('api')->plainTextToken,
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 3 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.alpha_dash' => 'Username hanya boleh berupa huruf, angka, tanda strip, atau garis bawah.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih yang lain.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi tidak valid.',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $user = User::query()->create([
            'name' => trim($data['name']),
            'username' => strtolower(trim($data['username'])),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'is_admin' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi pengguna berhasil!',
            'data' => [
                'user' => $user->only(['id', 'name', 'username', 'email', 'is_admin']),
                'token' => $user->createToken('api')->plainTextToken,
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully', 'data' => null]);
    }
}
