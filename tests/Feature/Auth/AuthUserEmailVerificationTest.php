<?php

namespace Feature\Auth;

use App\Models\Users\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthUserEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifyEmailSuccessfullyMarksEmailAsVerifiedAndFiresEvent(): void
    {
        // Prevent the real event from firing so we can assert on it
        Event::fake([Verified::class]);

        // Cria um usuário ainda não verificado
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Gera o hash e a URL assinada
        $hash = sha1($user->getEmailForVerification());
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash]
        );

        // Chama o endpoint
        $response = $this->getJson($url);

        // Valida resposta e payload
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'E-mail verificado com sucesso!',
            ]);

        // Verifica no banco que o e-mail foi marcado como verificado
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // Assegura que o evento Verified foi disparado com o usuário correto
        Event::assertDispatched(Verified::class, fn($e) => $e->user->is($user));
    }

    public function testVerifyEmailFailsWithInvalidSignature(): void
    {
        // Cria um usuário ainda não verificado
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Gera o hash, mas NÃO assina a URL
        $hash = sha1($user->getEmailForVerification());
        $url = route('verification.verify', ['id' => $user->id, 'hash' => $hash]);

        // Chama o endpoint sem assinatura válida
        $response = $this->getJson($url);

        // Esperamos 422 e erro no campo "message"
        $response->assertStatus(403);


        // Garante que o e-mail permanece não verificado
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function testVerifyEmailReturnsAlreadyVerifiedMessage(): void
    {
        // Cria um usuário que já tem email_verified_at preenchido
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Gera URL assinada normalmente
        $hash = sha1($user->getEmailForVerification());
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash]
        );

        // Chama o endpoint
        $response = $this->getJson($url);

        // Devolve mensagem de já verificado
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'E-mail já foi verificado.',
            ]);
    }

    public function testResendVerificationEmailSendsNotification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('verification.send'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Link de verificação reenviado!',
            ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function testResendVerificationEmailFailsIfAlreadyVerified(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('verification.send'));

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'E-mail já está verificado.',
            ]);
    }
}
