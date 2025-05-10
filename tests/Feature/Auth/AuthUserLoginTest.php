<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginReturnsTokenAndSuccessMessage(): void
    {
        // Arrange: cria um usuário com senha conhecida
        $user = User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $payload = [
            'email' => 'joao@example.com',
            'password' => 'Secret123!',
        ];

        // Act: tenta logar
        $response = $this->postJson('/api/auth/login', $payload);

        // Assert: resposta 200 e estrutura esperada
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message', 'token'])
            ->assertJson([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!',
            ]);

        // Garante que um token foi gerado
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        // Arrange: cria um usuário para testar credenciais inválidas
        User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('Secret123!'),
        ]);

        $payload = [
            'email' => 'joao@example.com',
            'password' => 'WrongPassword',
        ];

        // Act: tentativa de login com senha errada
        $response = $this->postJson('/api/auth/login', $payload);

        // Assert: lança ValidationException e retorna 422 com erro em "email"
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
