<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {
    protected $fillable = ['ad_id', 'email', 'last_known_price'];
    public $timestamps = false;

    public function ad() {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}
