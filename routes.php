<?php

use Illuminate\Support\Facades\Route;
use roilafx\Commerced3\Controllers\Commerced3Controller;
use roilafx\Commerced3\Controllers\ApiController;

Route::prefix('api')->group(function () {
    Route::get('/revenue',  [ApiController::class, 'revenue']);
    Route::get('/heatmap',  [ApiController::class, 'heatmap']);
    Route::get('/funnel',   [ApiController::class, 'funnel']);
    Route::get('/treemap',  [ApiController::class, 'treemap']);
    Route::get('/products', [ApiController::class, 'products']);
    Route::get('/metrics',  [ApiController::class, 'metrics']);
    Route::get('/sankey', [ApiController::class, 'sankey']);
});
Route::get('', [Commerced3Controller::class, 'index']);
