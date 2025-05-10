<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthUserForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function testForgotPasswordSendsResetLinkSuccessfully(): void
    {
        // Arrange: cria usuário no banco
        $user = User::factory()->create();

        // Mock do broker de senha para retornar sucesso
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andReturn(Password::RESET_LINK_SENT);

        // Act: chama o endpoint
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        // Assert: resposta 200 com JSON de sucesso
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Link de recuperação enviado com sucesso! Verifique seu e-mail.',
            ]);
    }

    public function testForgotPasswordValidationFailsWhenEmailMissing(): void
    {
        // Act: chamada sem payload
        $response = $this->postJson('/api/auth/forgot-password', []);

        // Assert: validação falha no campo email
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testForgotPasswordValidationFailsWhenEmailNotExists(): void
    {
        // Act: email não cadastrado
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'naoexiste@example.com',
        ]);

        // Assert: validação falha em exists:users,email
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testForgotPasswordReturnsServerErrorWhenBrokerFails(): void
    {
        // Arrange: cria usuário
        $user = User::factory()->create();

        // Mock do broker retornando erro
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andReturn('error');

        // Act
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ]);

        // Assert: erro 500 com JSON de erro
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Não foi possível enviar o link de recuperação. Tente novamente mais tarde.',
            ]);
    }
}
