<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreAuthRequest;
use App\Models\Users\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;


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

        event(new Registered($user));


        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Usuário criado com sucesso!',
            'token' => $token,
        ], 201);
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login realizado com sucesso!',
            'token' => $user->createToken('auth-token')->plainTextToken,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if (!URL::hasValidSignature($request)) {
            throw ValidationException::withMessages([
                'message' => ['Link de verificação inválido ou expirado.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'E-mail já foi verificado.',
            ]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'status' => 'success',
            'message' => 'E-mail verificado com sucesso!',
        ]);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'E-mail já está verificado.',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'message' => 'Link de verificação reenviado!',
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status !== Password::RESET_LINK_SENT) {
                throw new Exception(__($status));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Link de recuperação enviado com sucesso! Verifique seu e-mail.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar link de reset de senha: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Não foi possível enviar o link de recuperação. Tente novamente mais tarde.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout realizado com sucesso!'
        ]);
    }

}
