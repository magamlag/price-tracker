<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ad;
use App\Models\Subscription;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_subscribe_to_price_change()
    {
        $response = $this->postJson('/subscribe', [
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Subscribed successfully!']);

        $adId = '12345';
        $this->assertDatabaseHas('ads', [
            'id' => $adId,
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'ad_id' => $adId,
            'email' => 'user@example.com',
        ]);
    }

    /** @test */
    public function subscription_requires_valid_url_and_email()
    {
        $response = $this->postJson('/subscribe', [
            'url' => 'invalid-url',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url', 'email']);
    }

    /** @test */
    public function user_can_subscribe_multiple_times_without_duplication()
    {
        $payload = [
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
            'email' => 'user@example.com',
        ];

        // Перший запит
        $this->postJson('/subscribe', $payload)->assertStatus(200);

        // Другий запит з тим самим ad_id та email
        $this->postJson('/subscribe', $payload)->assertStatus(200);

        // Перевірка, що в базі лише один запис для цієї підписки
        $this->assertDatabaseCount('subscriptions', 1);
    }
}
