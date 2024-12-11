<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Ad;
use App\Models\Subscription;
use App\Services\AdPriceChecker;
use Illuminate\Support\Facades\Mail;
use App\Mail\PriceChangedMail;

class AdPriceCheckerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_email_when_price_changes()
    {
        Mail::fake();

        // Створення оголошення та підписки
        $ad = Ad::create([
            'id' => '12345',
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
        ]);

        Subscription::create([
            'ad_id' => '12345',
            'email' => 'user@example.com',
            'last_known_price' => 100.00,
        ]);

        // Мокаємо метод fetchPrice, щоб повернути нову ціну
        $checker = \Mockery::mock(AdPriceChecker::class)->makePartial();
        $checker->shouldReceive('fetchPrice')->andReturn(150.00);

        // Виклик перевірки цін
        $checker->checkPrices();

        // Перевірка, що електронний лист був надісланий
        Mail::assertSent(PriceChangedMail::class, function ($mail) use ($ad) {
            return $mail->hasTo('user@example.com') &&
                $mail->url === $ad->url &&
                $mail->price === 150.00;
        });

        // Перевірка, що остання відома ціна оновилася
        $this->assertDatabaseHas('subscriptions', [
            'ad_id' => '12345',
            'email' => 'user@example.com',
            'last_known_price' => 150.00,
        ]);
    }

    /** @test */
    public function it_does_not_send_email_when_price_does_not_change()
    {
        Mail::fake();

        // Створення оголошення та підписки
        $ad = Ad::create([
            'id' => '12345',
            'url' => 'https://www.olx.ua/d/uk/obyavlenie/12345',
        ]);

        Subscription::create([
            'ad_id' => '12345',
            'email' => 'user@example.com',
            'last_known_price' => 100.00,
        ]);

        // Мокаємо метод fetchPrice, щоб повернути ту ж ціну
        $checker = \Mockery::mock(AdPriceChecker::class)->makePartial();
        $checker->shouldReceive('fetchPrice')->andReturn(100.00);

        // Виклик перевірки цін
        $checker->checkPrices();

        // Перевірка, що електронний лист не був надісланий
        Mail::assertNothingSent();

        // Перевірка, що остання відома ціна не змінилася
        $this->assertDatabaseHas('subscriptions', [
            'ad_id' => '12345',
            'email' => 'user@example.com',
            'last_known_price' => 100.00,
        ]);
    }
}
