<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Facades\Mail;

class AdPriceChecker {
    public function checkPrices() {
        $ads = Ad::with('subscriptions')->get();

        foreach ($ads as $ad) {
            $currentPrice = $this->fetchPrice($ad->url);

            foreach ($ad->subscriptions as $subscription) {
                if ($subscription->last_known_price !== $currentPrice) {
                    $this->sendPriceChangeEmail($subscription->email, $ad->url, $currentPrice);
                    $subscription->update(['last_known_price' => $currentPrice]);
                }
            }

            $ad->update(['last_checked' => now()]);
        }
    }

    private function fetchPrice(string $url): float {
        // Реалізація парсингу HTML або виклику API OLX
        $html = file_get_contents($url);
        preg_match('/"price":"([\d\.]+)"/', $html, $matches);
        return isset($matches[1]) ? (float)$matches[1] : throw new \RuntimeException("Price not found");
    }

    private function sendPriceChangeEmail(string $email, string $url, float $price) {
        Mail::to($email)->send(new \App\Mail\PriceChangedMail($url, $price));
    }
}
