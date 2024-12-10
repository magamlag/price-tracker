<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model {
    protected $fillable = ['id', 'url', 'last_checked'];
    public $incrementing = false;

    public function subscriptions() {
        return $this->hasMany(Subscription::class, 'ad_id');
    }
}
