<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\PasarelaDePago;
use App\Mail\ReservaConfirmada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    /**
     * Obtener configuración pública de Wompi
     */
    public function getWompiConfig()
    {
        $publicKey = env('WOMPI_PUBLIC_KEY');

        Log::info('getWompiConfig llamado', [
            'publicKey_exists' => !empty($publicKey),
            'publicKey_preview' => $publicKey ? substr($publicKey, 0, 20) . '...' : 'NULL'
        ]);

        return response()->json([
            'success' => true,
            'publicKey' => $publicKey,
            'currency' => 'COP'
        ]);
    }

    /**
     * Crear transacción en Wompi con firma de integridad
     */
    public function createWompiTransaction(Request $request)
    {
        // Validación
        $validated = $request->validate([
            'amount_in_cents' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'customer_email' => 'required|email',
            'reference' => 'required|string',
        ]);

        try {
            // Generar reference única
            $reference = $validated['reference'];
            $amountInCents = (int) $validated['amount_in_cents'];
            $currency = $validated['currency'];

            // Obtener el integrity secret desde el .env
            $integritySecret = env('WOMPI_INTEGRITY_SECRET');

            // IMPORTANTE: Verificar que el integrity secret no esté vacío
            if (empty($integritySecret)) {
                throw new \Exception('WOMPI_INTEGRITY_SECRET no está configurado en el archivo .env');
            }

            Log::info('=== GENERANDO FIRMA WOMPI ===');
            Log::info('Integrity Secret (primeros 20 chars): ' . substr($integritySecret, 0, 20) . '...');

            // Generar firma de integridad según documentación de Wompi
            // Formato EXACTO: reference + amount_in_cents + currency + integrity_secret
            // TODOS los valores deben estar en el orden correcto SIN espacios ni separadores
            $integrityString = $reference . $amountInCents . $currency . $integritySecret;
            $integritySignature = hash('sha256', $integrityString);

            Log::info('Datos para firma:', [
                'reference' => $reference,
                'amount_in_cents' => $amountInCents,
                'currency' => $currency,
                'string_concatenada' => substr($integrityString, 0, 100) . '...',
                'signature_generada' => $integritySignature
            ]);

            // Retornar datos para el widget de Wompi
            return response()->json([
                'success' => true,
                'message' => 'Datos preparados para Wompi',
                'data' => [
                    'public_key' => env('WOMPI_PUBLIC_KEY'),
                    'amount_in_cents' => $amountInCents,
                    'currency' => $currency,
                    'reference' => $reference,
                    'customer_email' => $validated['customer_email'],
                    'redirect_url' => 'http://localhost:3000/confirmacion',
                    'integrity_signature' => $integritySignature
                ],
                'transaction_id' => $reference
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en createWompiTransaction: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al preparar el pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook de Wompi para recibir notificaciones
     */
    public function wompiWebhook(Request $request)
    {
        Log::info('Wompi Webhook recibido:', $request->all());

        try {
            // Wompi envía los datos en el evento
            $event = $request->input('event');
            $data = $request->input('data.transaction');

            if (!$data) {
                return response()->json(['message' => 'Datos inválidos'], 400);
            }

            // Extraer información
            $reference = $data['reference'] ?? '';
            $status = $data['status'] ?? '';
            $transactionId = $data['id'] ?? '';

            Log::info('Transacción procesada:', [
                'reference' => $reference,
                'status' => $status,
                'transaction_id' => $transactionId
            ]);

            // Aquí puedes agregar lógica para actualizar tu base de datos
            // Por ejemplo, buscar la reserva por reference y actualizar su estado

            return response()->json(['message' => 'Webhook procesado correctamente'], 200);

        } catch (\Exception $e) {
            Log::error('Error en webhook de Wompi: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error al procesar webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar estado de transacción en Wompi
     */
    public function getTransactionStatus($transactionId)
    {
        try {
            $publicKey = env('WOMPI_PUBLIC_KEY');

            $apiUrl = env('WOMPI_API_URL', 'https://sandbox.wompi.co/v1');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $publicKey,
            ])->get($apiUrl . '/transactions/' . $transactionId);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el estado de la transacción',
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar reserva después del pago exitoso
     */
    public function confirmarReserva(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'num_adultos' => 'required|integer|min:1',
            'num_ninos' => 'required|integer|min:0',
            'precio_total' => 'required|numeric|min:0',
            'cliente' => 'required|array',
            'cliente.cedula' => 'required|string',
            'cliente.nombre' => 'required|string',
            'cliente.apellido' => 'required|string',
            'cliente.email' => 'required|email',
            'cliente.celular' => 'required|string',
            'cliente.telefono' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Verificar el estado de la transacción en Wompi
            $publicKey = env('WOMPI_PUBLIC_KEY');
            $apiUrl = env('WOMPI_API_URL', 'https://sandbox.wompi.co/v1');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $publicKey,
            ])->get($apiUrl . '/transactions/' . $validated['transaction_id']);

            if (!$response->successful()) {
                throw new \Exception('No se pudo verificar el estado del pago');
            }

            $transaction = $response->json();
            $status = $transaction['data']['status'] ?? '';

            if ($status !== 'APPROVED') {
                throw new \Exception('El pago no ha sido aprobado. Estado: ' . $status);
            }

            // Buscar una habitación disponible del tipo solicitado
            $habitacionesDisponibles = \App\Models\Habitacion::where('tipo_habitacion_id', $validated['tipo_habitacion_id'])
                ->where('estado', 'disponible')
                ->whereDoesntHave('reservas', function($query) use ($validated) {
                    $query->where('estado', '!=', 'cancelada')
                          ->where('fecha_entrada', '<', $validated['fecha_salida'])
                          ->where('fecha_salida', '>', $validated['fecha_entrada']);
                })
                ->get();

            if ($habitacionesDisponibles->isEmpty()) {
                throw new \Exception('No hay habitaciones disponibles para las fechas seleccionadas');
            }

            // Tomar la primera habitación disponible
            $habitacion = $habitacionesDisponibles->first();

            // Crear o actualizar el cliente
            $cliente = \App\Models\Cliente::updateOrCreate(
                ['cedula' => $validated['cliente']['cedula']],
                [
                    'nombre' => $validated['cliente']['nombre'],
                    'apellido' => $validated['cliente']['apellido'],
                    'email' => $validated['cliente']['email'],
                    'telefono' => $validated['cliente']['celular'], // Usar celular como telefono
                ]
            );

            // Crear la reserva
            $reserva = Reserva::create([
                'cliente_cedula' => $cliente->cedula,
                'habitacion_id' => $habitacion->id,
                'fecha_entrada' => $validated['fecha_entrada'],
                'fecha_salida' => $validated['fecha_salida'],
                'num_adultos' => $validated['num_adultos'],
                'num_ninos' => $validated['num_ninos'],
                'precio_total' => $validated['precio_total'],
                'estado' => 'pagada', // Marcar como pagada inmediatamente
            ]);

            // Crear registro de pago
            $pago = \App\Models\Pago::create([
                'reserva_id' => $reserva->id,
                'monto' => $validated['precio_total'],
                'metodo_pago' => 'wompi',
                'estado' => 'aprobado',
                'codigo_transaccion' => $validated['transaction_id'],
                'detalles' => json_encode($transaction['data'] ?? []),
            ]);

            // Actualizar estado de la habitación
            $habitacion->estado = 'ocupada';
            $habitacion->save();

            DB::commit();

            Log::info('Reserva creada exitosamente:', [
                'reserva_id' => $reserva->id,
                'cliente' => $cliente->cedula,
                'habitacion' => $habitacion->id,
                'transaction_id' => $validated['transaction_id']
            ]);

            // Enviar email de confirmación
            try {
                $reserva->load('habitacion');
                Mail::to($cliente->email)->send(new ReservaConfirmada($reserva, $cliente));
                Log::info('Email de confirmación enviado a: ' . $cliente->email);
            } catch (\Exception $e) {
                Log::error('Error al enviar email de confirmación: ' . $e->getMessage());
                // No fallar si el email falla, solo registrar el error
            }

            return response()->json([
                'success' => true,
                'message' => '¡Su reserva ha sido adquirida con éxito!',
                'data' => [
                    'reserva_id' => $reserva->id,
                    'habitacion' => $habitacion->numero_habitacion,
                    'cliente' => $cliente->nombre . ' ' . $cliente->apellido,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'precio_total' => $reserva->precio_total,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al confirmar reserva: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar comprobante de reserva en PDF
     */
    public function descargarComprobante($reservaId)
    {
        try {
            // Buscar la reserva con sus relaciones
            $reserva = Reserva::with(['cliente', 'habitacion'])->findOrFail($reservaId);
            $cliente = $reserva->cliente;

            // Generar el PDF
            $pdf = Pdf::loadView('pdf.reserva-confirmacion', compact('reserva', 'cliente'));

            // Descargar el PDF
            return $pdf->download('confirmacion-reserva-' . $reserva->id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de confirmación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
