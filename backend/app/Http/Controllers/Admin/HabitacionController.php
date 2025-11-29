<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HabitacionController extends Controller
{
    /**
     * Listar habitaciones con filtros por estado
     */
    public function index(Request $request)
    {
        try {
            $query = Habitacion::with(['tipoHabitacion']);

            // Filtro por estado
            if ($request->has('estado') && $request->estado != '') {
                $query->where('estado', $request->estado);
            }

            // Filtro por tipo de habitación
            if ($request->has('tipo_habitacion_id')) {
                $query->where('tipo_habitacion_id', $request->tipo_habitacion_id);
            }

            // Filtro por piso
            if ($request->has('piso')) {
                $query->where('piso', $request->piso);
            }

            $habitaciones = $query->get()->map(function($habitacion) {
                $reservaActual = Reserva::where('habitacion_id', $habitacion->id)
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->whereIn('estado', ['pagada', 'pendiente', 'en_curso'])
                    ->with('cliente')
                    ->first();

                return [
                    'id' => $habitacion->id,
                    'numero' => $habitacion->numero_habitacion,
                    'piso' => $habitacion->piso,
                    'estado' => $habitacion->estado,
                    'tipo' => [
                        'id' => $habitacion->tipoHabitacion->id,
                        'nombre' => $habitacion->tipoHabitacion->nombre,
                        'precio_base' => $habitacion->tipoHabitacion->precio_base,
                        'es_apartamento' => $habitacion->tipoHabitacion->es_apartamento
                    ],
                    'reserva_actual' => $reservaActual ? [
                        'id' => $reservaActual->id,
                        'cliente' => $reservaActual->cliente->nombre . ' ' . $reservaActual->cliente->apellido,
                        'fecha_entrada' => $reservaActual->fecha_entrada,
                        'fecha_salida' => $reservaActual->fecha_salida,
                        'estado' => $reservaActual->estado
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'habitaciones' => $habitaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar habitaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar habitaciones'
            ], 500);
        }
    }

    /**
     * Crear nueva habitación
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'numero_habitacion' => 'required|string|max:10|unique:habitacions,numero_habitacion',
                'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
                'piso' => 'required|integer|min:1',
                'estado' => 'nullable|in:disponible,ocupada,mantenimiento,limpieza,bloqueada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $habitacion = Habitacion::create([
                'numero_habitacion' => $request->numero_habitacion,
                'tipo_habitacion_id' => $request->tipo_habitacion_id,
                'piso' => $request->piso,
                'estado' => $request->estado ?? 'disponible'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Habitación creada exitosamente',
                'habitacion' => $habitacion->load('tipoHabitacion')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear habitación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear habitación'
            ], 500);
        }
    }

    /**
     * Actualizar habitación
     */
    public function update(Request $request, $id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'numero_habitacion' => 'sometimes|string|max:10|unique:habitacions,numero_habitacion,' . $id,
                'tipo_habitacion_id' => 'sometimes|exists:tipo_habitacions,id',
                'piso' => 'sometimes|integer|min:1',
                'estado' => 'sometimes|in:disponible,ocupada,mantenimiento,limpieza,bloqueada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $habitacion->update($request->only(['numero_habitacion', 'tipo_habitacion_id', 'piso', 'estado']));

            return response()->json([
                'success' => true,
                'message' => 'Habitación actualizada exitosamente',
                'habitacion' => $habitacion->load('tipoHabitacion')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar habitación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar habitación'
            ], 500);
        }
    }

    /**
     * Eliminar habitación
     */
    public function destroy($id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            // Verificar que no tenga reservas activas o futuras
            $reservasActivas = Reserva::where('habitacion_id', $id)
                ->where('fecha_salida', '>=', now())
                ->whereIn('estado', ['pagada', 'pendiente', 'en_curso'])
                ->exists();

            if ($reservasActivas) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar. La habitación tiene reservas activas o futuras.'
                ], 400);
            }

            $habitacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Habitación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar habitación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar habitación'
            ], 500);
        }
    }

    /**
     * Cambiar estado de habitación
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:disponible,ocupada,mantenimiento,limpieza,bloqueada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $habitacion = Habitacion::findOrFail($id);

            // Verificar si hay reserva activa
            $reservaActiva = Reserva::where('habitacion_id', $id)
                ->where('fecha_entrada', '<=', now())
                ->where('fecha_salida', '>=', now())
                ->whereIn('estado', ['pagada', 'pendiente', 'en_curso'])
                ->exists();

            if ($request->estado !== 'ocupada' && $request->estado !== 'limpieza' && $reservaActiva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar el estado. Hay una reserva activa para esta habitación.'
                ], 400);
            }

            $habitacion->estado = $request->estado;
            $habitacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'habitacion' => $habitacion->load('tipoHabitacion')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }
    }

    /**
     * Estadísticas de habitaciones
     */
    public function stats()
    {
        try {
            $total = Habitacion::count();
            $disponibles = Habitacion::where('estado', 'disponible')->count();
            $ocupadas = Habitacion::where('estado', 'ocupada')->count();
            $mantenimiento = Habitacion::where('estado', 'mantenimiento')->count();
            $limpieza = Habitacion::where('estado', 'limpieza')->count();
            $bloqueadas = Habitacion::where('estado', 'bloqueada')->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'disponibles' => $disponibles,
                    'ocupadas' => $ocupadas,
                    'mantenimiento' => $mantenimiento,
                    'limpieza' => $limpieza,
                    'bloqueadas' => $bloqueadas,
                    'tasa_ocupacion' => $total > 0 ? round(($ocupadas / $total) * 100, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas'
            ], 500);
        }
    }

    /**
     * Obtener precios dinámicos
     */
    public function getPricing($id)
    {
        try {
            $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($id);

            return response()->json([
                'success' => true,
                'precio_base' => $habitacion->tipoHabitacion->precio_base,
                'tipo_habitacion' => $habitacion->tipoHabitacion->nombre
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener precios'
            ], 500);
        }
    }

    /**
     * Obtener cola de limpieza
     */
    public function getLimpiezaQueue()
    {
        try {
            $habitaciones = Habitacion::with(['tipoHabitacion'])
                ->where('estado', 'limpieza')
                ->get()
                ->map(function($habitacion) {
                    return [
                        'id' => $habitacion->id,
                        'numero' => $habitacion->numero_habitacion,
                        'piso' => $habitacion->piso,
                        'tipo' => $habitacion->tipoHabitacion->nombre,
                        'prioridad' => 'normal' // Podría implementarse lógica de prioridad
                    ];
                });

            return response()->json([
                'success' => true,
                'cola_limpieza' => $habitaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener cola de limpieza: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar cola de limpieza'
            ], 500);
        }
    }

    /**
     * Marcar habitación como limpia
     */
    public function markAsClean($id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            if ($habitacion->estado !== 'limpieza') {
                return response()->json([
                    'success' => false,
                    'message' => 'La habitación no está en estado de limpieza'
                ], 400);
            }

            $habitacion->estado = 'disponible';
            $habitacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Habitación marcada como limpia',
                'habitacion' => $habitacion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al marcar como limpia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado'
            ], 500);
        }
    }

    /**
     * Bloquear habitación por fechas
     */
    public function block(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'motivo' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $habitacion = Habitacion::findOrFail($id);
            $habitacion->estado = 'bloqueada';
            $habitacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Habitación bloqueada exitosamente',
                'habitacion' => $habitacion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al bloquear habitación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al bloquear habitación'
            ], 500);
        }
    }

    /**
     * Desbloquear habitación
     */
    public function unblock($id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            if ($habitacion->estado !== 'bloqueada') {
                return response()->json([
                    'success' => false,
                    'message' => 'La habitación no está bloqueada'
                ], 400);
            }

            $habitacion->estado = 'disponible';
            $habitacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Habitación desbloqueada exitosamente',
                'habitacion' => $habitacion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al desbloquear habitación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desbloquear habitación'
            ], 500);
        }
    }

    /**
     * Obtener historial de mantenimiento
     */
    public function getMantenimiento($id)
    {
        try {
            $habitacion = Habitacion::findOrFail($id);

            // Por ahora retornamos un array vacío
            // En una implementación completa, habría una tabla de mantenimiento
            return response()->json([
                'success' => true,
                'historial' => [],
                'estado_actual' => $habitacion->estado
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historial de mantenimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar historial'
            ], 500);
        }
    }

    /**
     * Método legacy - mantener compatibilidad
     */
    public function cambiarEstado(Request $request, $id)
    {
        return $this->updateStatus($request, $id);
    }
}
