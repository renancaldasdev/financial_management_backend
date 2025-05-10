<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthUserLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function testLogoutDeletesCurrentAccessToken(): void
    {
        // Arrange: cria usuário e gera token
        $user = User::factory()->create();
        $tokenResult = $user->createToken('test-token');
        $plainTextToken = $tokenResult->plainTextToken;
        $tokenName = $tokenResult->accessToken->name;

        // Act: chama endpoint logout com token
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainTextToken)
            ->postJson('/api/auth/logout');

        // Assert: resposta 200 e mensagem de sucesso
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logout realizado com sucesso!',
            ]);

        // Verifica que o token foi deletado do banco
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => $tokenName,
        ]);
    }

    public function testLogoutRequiresAuthentication(): void
    {
        // Act: chama logout sem autenticação
        $response = $this->postJson('/api/auth/logout');

        // Assert: deve retornar 401 Unauthorized
        $response->assertStatus(401);
    }
}
