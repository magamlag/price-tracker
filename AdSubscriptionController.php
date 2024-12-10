<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdSubscriptionController extends Controller {
    public function subscribe(Request $request) {
        $validated = $request->validate([
            'url' => 'required|url',
            'email' => 'required|email'
        ]);

        $adId = $this->extractAdId($validated['url']);
        $ad = Ad::firstOrCreate(['id' => $adId], ['url' => $validated['url']]);

        Subscription::updateOrCreate(
            ['ad_id' => $adId, 'email' => $validated['email']],
            ['last_known_price' => null]
        );

        return response()->json(['message' => 'Subscribed successfully!']);
    }

    private function extractAdId(string $url): string {
        preg_match('/\/(\d+)$/', $url, $matches);
        return $matches[1] ?? throw new \InvalidArgumentException("Invalid URL");
    }
}
