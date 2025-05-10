<?php

declare(strict_types=1);

namespace Feature\Auth;

use App\Models\Users\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthUserRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistrationReturnsTokenAndFiresEvent(): void
    {
        Event::fake();

        $payload = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'Secret123!',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'message', 'token'])
            ->assertJson([
                'status' => 'success',
                'message' => 'Usuário criado com sucesso!',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'name' => 'João Silva',
        ]);

        $user = User::where('email', 'joao@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Secret123!', $user->password));

        Event::assertDispatched(Registered::class, fn($e) => $e->user->is($user));

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function testValidationFailsWhenRequiredFieldsAreMissing(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }
}
