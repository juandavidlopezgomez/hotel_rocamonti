<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Obtener todas las reservas (con filtros opcionales)
     */
    public function index(Request $request)
    {
        try {
            $query = Reserva::with(['cliente', 'habitacion.tipoHabitacion', 'pago']);

            // Filtrar por estado
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Filtrar por cliente (cédula)
            if ($request->has('cliente_cedula')) {
                $query->where('cliente_cedula', $request->cliente_cedula);
            }

            // Filtrar por fechas
            if ($request->has('fecha_desde')) {
                $query->where('fecha_entrada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('fecha_salida', '<=', $request->fecha_hasta);
            }

            $reservas = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener reservas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reservas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una reserva específica por ID
     */
    public function show($id)
    {
        try {
            $reserva = Reserva::with(['cliente', 'habitacion.tipoHabitacion', 'pago'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }
    }

    /**
     * Buscar reserva por código de transacción
     */
    public function buscarPorTransaccion($codigoTransaccion)
    {
        try {
            $reserva = Reserva::whereHas('pago', function($query) use ($codigoTransaccion) {
                $query->where('codigo_transaccion', $codigoTransaccion);
            })->with(['cliente', 'habitacion.tipoHabitacion', 'pago'])->first();

            if (!$reserva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró ninguna reserva con ese código de transacción'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modificar una reserva existente
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'fecha_entrada' => 'sometimes|date',
            'fecha_salida' => 'sometimes|date|after:fecha_entrada',
            'num_adultos' => 'sometimes|integer|min:1',
            'num_ninos' => 'sometimes|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $reserva = Reserva::findOrFail($id);

            // Solo se pueden modificar reservas que no estén canceladas
            if ($reserva->estado === 'cancelada') {
                throw new \Exception('No se puede modificar una reserva cancelada');
            }

            // Si se están cambiando las fechas, verificar disponibilidad
            if (isset($validated['fecha_entrada']) || isset($validated['fecha_salida'])) {
                $fechaEntrada = $validated['fecha_entrada'] ?? $reserva->fecha_entrada;
                $fechaSalida = $validated['fecha_salida'] ?? $reserva->fecha_salida;

                // Verificar que la habitación esté disponible en las nuevas fechas
                $conflictos = Reserva::where('habitacion_id', $reserva->habitacion_id)
                    ->where('id', '!=', $reserva->id)
                    ->where('estado', '!=', 'cancelada')
                    ->where(function($query) use ($fechaEntrada, $fechaSalida) {
                        $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                              ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                              ->orWhere(function($q) use ($fechaEntrada, $fechaSalida) {
                                  $q->where('fecha_entrada', '<=', $fechaEntrada)
                                    ->where('fecha_salida', '>=', $fechaSalida);
                              });
                    })
                    ->count();

                if ($conflictos > 0) {
                    throw new \Exception('La habitación no está disponible para las fechas seleccionadas');
                }

                $reserva->fecha_entrada = $fechaEntrada;
                $reserva->fecha_salida = $fechaSalida;

                // Recalcular precio si cambió el número de noches
                $noches = Carbon::parse($fechaEntrada)->diffInDays(Carbon::parse($fechaSalida));
                $precioBase = $reserva->habitacion->tipoHabitacion->precio_base;
                $reserva->precio_total = $precioBase * $noches;
            }

            if (isset($validated['num_adultos'])) {
                $reserva->num_adultos = $validated['num_adultos'];
            }

            if (isset($validated['num_ninos'])) {
                $reserva->num_ninos = $validated['num_ninos'];
            }

            $reserva->save();

            DB::commit();

            Log::info('Reserva modificada exitosamente:', ['reserva_id' => $reserva->id]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva modificada exitosamente',
                'reserva' => $reserva->load(['cliente', 'habitacion.tipoHabitacion', 'pago'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al modificar reserva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al modificar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar una reserva
     */
    public function cancelar($id)
    {
        DB::beginTransaction();

        try {
            $reserva = Reserva::findOrFail($id);

            // Verificar que no esté ya cancelada
            if ($reserva->estado === 'cancelada') {
                throw new \Exception('La reserva ya está cancelada');
            }

            // Actualizar estado de la reserva
            $reserva->estado = 'cancelada';
            $reserva->save();

            // Liberar la habitación
            $habitacion = $reserva->habitacion;
            $habitacion->estado = 'disponible';
            $habitacion->save();

            DB::commit();

            Log::info('Reserva cancelada exitosamente:', ['reserva_id' => $reserva->id]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar reserva: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reservas de un cliente específico
     */
    public function reservasCliente($cedula)
    {
        try {
            $reservas = Reserva::where('cliente_cedula', $cedula)
                ->with(['habitacion.tipoHabitacion', 'pago'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las reservas del cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
