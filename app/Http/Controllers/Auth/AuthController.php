<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreAuthRequest;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(StoreAuthRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'user_name' => $data['user_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Usuário criado com sucesso!',
            'token' => $token,
        ], 201);
    }

    public function login()
    {

    }

    public function forgotPassword()
    {
    }

    public function logout()
    {
    }

}
