<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReservaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta de test
Route::get('/test', function () {
    return response()->json([
        'mensaje' => 'Hotel Rocamonti API funcionando',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
      ->header('Access-Control-Allow-Headers', '*');
});

// RUTA DE EMERGENCIA - SIN MIDDLEWARE
Route::get('/room-types-directo', function () {
    try {
        $tipos = \App\Models\TipoHabitacion::with('habitaciones')->get();

        $resultado = $tipos->map(function($tipo) {
            return [
                'id' => $tipo->id,
                'nombre' => $tipo->nombre,
                'descripcion_camas' => $tipo->descripcion_camas,
                'capacidad_adultos' => $tipo->capacidad_adultos,
                'capacidad_ninos' => $tipo->capacidad_ninos,
                'precio_base' => (float) $tipo->precio_base,
                'es_apartamento' => (bool) $tipo->es_apartamento,
                'metros_cuadrados' => $tipo->metros_cuadrados,
                'amenidades' => $tipo->amenidades ?? [],
                'habitaciones_disponibles' => $tipo->habitaciones->where('estado', 'disponible')->count()
            ];
        });

        return response()->json([
            'success' => true,
            'tipos' => $resultado->values(),
            'message' => 'Ruta directa sin middleware'
        ])->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', '*');
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500)->header('Access-Control-Allow-Origin', '*')
          ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
          ->header('Access-Control-Allow-Headers', '*');
    }
});

// Ruta de test para verificar configuración de Wompi
Route::get('/test-wompi', function () {
    $integritySecret = config('services.wompi.integrity_secret', env('WOMPI_INTEGRITY_SECRET'));
    $publicKey = config('services.wompi.public_key', env('WOMPI_PUBLIC_KEY'));

    // Generar firma de prueba
    $reference = 'TEST-' . time();
    $amount = 100000;
    $currency = 'COP';

    $concatenated = $reference . $amount . $currency . $integritySecret;
    $signature = hash('sha256', $concatenated);

    return response()->json([
        'integrity_secret_loaded' => !empty($integritySecret),
        'integrity_secret_preview' => substr($integritySecret, 0, 20) . '...',
        'public_key_preview' => substr($publicKey, 0, 20) . '...',
        'test_data' => [
            'reference' => $reference,
            'amount' => $amount,
            'currency' => $currency,
            'concatenated_string_preview' => substr($concatenated, 0, 50) . '...',
            'signature' => $signature
        ]
    ]);
});

// Rutas de habitaciones
Route::get('/room-types', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::get('/rooms/{id}/occupied-dates', [RoomController::class, 'getOccupiedDates']);

// Rutas de pagos con Wompi
Route::get('/payments/wompi/config', [PaymentController::class, 'getWompiConfig']);
Route::post('/payments/wompi/create', [PaymentController::class, 'createWompiTransaction']);
Route::post('/payments/wompi/webhook', [PaymentController::class, 'wompiWebhook']);
Route::get('/payments/wompi/{transactionId}', [PaymentController::class, 'getTransactionStatus']);
Route::post('/payments/confirmar-reserva', [PaymentController::class, 'confirmarReserva']);

// Rutas de reservas
Route::get('/reservas', [ReservaController::class, 'index']);
Route::get('/reservas/{id}', [ReservaController::class, 'show']);
Route::get('/reservas/{id}/comprobante', [PaymentController::class, 'descargarComprobante']);
Route::get('/reservas/transaccion/{codigoTransaccion}', [ReservaController::class, 'buscarPorTransaccion']);
Route::get('/reservas/cliente/{cedula}', [ReservaController::class, 'reservasCliente']);
Route::put('/reservas/{id}', [ReservaController::class, 'update']);
Route::post('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);

// Rutas del Dashboard Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HabitacionController as AdminHabitacionController;
use App\Http\Controllers\Admin\ClienteController;

// Dashboard
Route::get('/admin/stats', [DashboardController::class, 'stats']);
Route::get('/admin/proximas-reservas', [DashboardController::class, 'proximasReservas']);
Route::get('/admin/ocupacion-semanal', [DashboardController::class, 'ocupacionSemanal']);
Route::get('/admin/top-habitaciones', [DashboardController::class, 'topHabitaciones']);
Route::post('/admin/generar-reporte', [DashboardController::class, 'generarReporte']);

// Habitaciones Admin
Route::get('/admin/habitaciones', [AdminHabitacionController::class, 'index']);
Route::get('/admin/habitaciones/stats', [AdminHabitacionController::class, 'stats']);
Route::put('/admin/habitaciones/{id}/estado', [AdminHabitacionController::class, 'cambiarEstado']);

// Clientes Admin
Route::get('/admin/clientes', [ClienteController::class, 'index']);
Route::get('/admin/clientes/stats', [ClienteController::class, 'stats']);
Route::get('/admin/clientes/{id}', [ClienteController::class, 'show']);
Route::get('/admin/clientes/{id}/historial', [ClienteController::class, 'historial']);
