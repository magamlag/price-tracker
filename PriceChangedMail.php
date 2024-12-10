<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PriceChangedMail extends Mailable {
    public function __construct(public string $url, public float $price) {}

    public function build() {
        return $this->view('emails.price_changed')
            ->subject('Зміна ціни оголошення')
            ->with(['url' => $this->url, 'price' => $this->price]);
    }
}
