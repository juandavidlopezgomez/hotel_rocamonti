<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'nombre' => 'Hotel Rocamonti API',
        'version' => '1.0.0',
        'endpoints' => [
            'api' => '/api',
            'test' => '/api/test',
            'rooms' => '/api/rooms',
            'bookings' => '/api/bookings',
        ]
    ]);
});

// Endpoint de prueba CORS
Route::get('/test-cors', function () {
    return response()->json([
        'success' => true,
        'message' => 'CORS está funcionando correctamente',
        'timestamp' => now()->toISOString(),
        'server_time' => date('Y-m-d H:i:s'),
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
});
