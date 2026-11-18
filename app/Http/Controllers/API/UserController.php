<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        return $this->success('Data pengguna berhasil diambil', new UserResource($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->success('Profil berhasil diperbarui', new UserResource($user->fresh()));
    }
}
