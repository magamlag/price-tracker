<?php

use App\Http\Controllers\AdSubscriptionController;

Route::post('/subscribe', [AdSubscriptionController::class, 'subscribe']);