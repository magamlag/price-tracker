<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionConfirmationMail;

class EmailConfirmationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_receives_confirmation_email_upon_subscription()
    {
        Mail::fake();

        $response = $this->postJson('/subscribe', [
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200);

        $subscription = Subscription::first();

        Mail::assertSent(SubscriptionConfirmationMail::class, function ($mail) use ($subscription) {
            return $mail->hasTo('user@example.com') &&
                $mail->subscription->id === $subscription->id;
        });
    }

    /** @test */
    public function user_can_confirm_subscription_via_confirmation_link()
    {
        Mail::fake();

        // Підписка
        $this->postJson('/subscribe', [
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
            'email' => 'user@example.com',
        ]);

        $subscription = Subscription::first();
        $token = $subscription->confirmation_token;

        // Перевірка підтвердження
        $response = $this->get('/confirm/' . $token);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email підтверджено успішно!']);

        $this->assertTrue($subscription->fresh()->is_confirmed);
        $this->assertNull($subscription->fresh()->confirmation_token);
    }
}
