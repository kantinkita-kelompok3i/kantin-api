<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated() + ['role' => 'customer']);

        // tugas mewajibkan register langsung mengembalikan token
        return $this->success('Registrasi berhasil', [
            'access_token' => $user->createToken('mobile')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            return $this->error('Email atau password salah', [], 401);
        }

        return $this->success('Login berhasil', [
            'access_token' => $user->createToken('mobile')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logout berhasil', null);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success('Data pengguna berhasil diambil', new UserResource($request->user()));
    }
}
