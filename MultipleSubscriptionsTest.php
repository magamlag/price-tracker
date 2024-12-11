<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ad;
use App\Models\Subscription;
use App\Services\AdPriceChecker;
use Illuminate\Support\Facades\Mail;
use App\Mail\PriceChangedMail;

class MultipleSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function multiple_subscriptions_to_same_ad_trigger_single_price_check()
    {
        Mail::fake();

        // Створення оголошення та декількох підписок
        $ad = Ad::create([
            'id' => '12345',
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
        ]);

        Subscription::create([
            'ad_id' => '12345',
            'email' => 'user1@example.com',
            'last_known_price' => 100.00,
        ]);

        Subscription::create([
            'ad_id' => '12345',
            'email' => 'user2@example.com',
            'last_known_price' => 100.00,
        ]);

        // Мокаємо метод fetchPrice, щоб повернути нову ціну
        $checker = \Mockery::mock(AdPriceChecker::class)->makePartial();
        $checker->shouldReceive('fetchPrice')->andReturn(150.00);

        // Виклик перевірки цін
        $checker->checkPrices();

        // Перевірка, що електронні листи були надіслані кожному користувачу
        Mail::assertSent(PriceChangedMail::class, 2);

        Mail::assertSent(PriceChangedMail::class, function ($mail) {
            return $mail->hasTo('user1@example.com') &&
                $mail->price === 150.00;
        });

        Mail::assertSent(PriceChangedMail::class, function ($mail) {
            return $mail->hasTo('user2@example.com') &&
                $mail->price === 150.00;
        });

        // Перевірка, що остання відома ціна оновилася для обох підписок
        $this->assertDatabaseHas('subscriptions', [
            'ad_id' => '12345',
            'email' => 'user1@example.com',
            'last_known_price' => 150.00,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'ad_id' => '12345',
            'email' => 'user2@example.com',
            'last_known_price' => 150.00,
        ]);
    }
}
