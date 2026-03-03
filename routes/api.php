<?php

use App\Http\Controllers\Webhooks\MetaWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Webhook endpoints for external platforms (Meta, Evolution API, etc.)
|
*/

// Meta Platform Webhooks (Facebook Messenger + Instagram DMs)
Route::prefix('webhooks/meta')->group(function () {
    Route::get('/', [MetaWebhookController::class, 'verify']);
    Route::post('/', [MetaWebhookController::class, 'handle']);
});
