<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    protected $reservaService;

    public function __construct(ReservaService $reservaService)
    {
        $this->reservaService = $reservaService;
    }

    /**
     * Listar todas las reservas con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = Reserva::with(['cliente', 'habitacion.tipoHabitacion', 'pago']);

            // Filtros
            if ($request->has('estado') && $request->estado != '') {
                $query->where('estado', $request->estado);
            }

            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('cliente', function ($qc) use ($search) {
                        $qc->where('nombre', 'like', "%{$search}%")
                           ->orWhere('apellido', 'like', "%{$search}%")
                           ->orWhere('cedula', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('id', 'like', "%{$search}%");
                });
            }

            if ($request->has('fecha_desde')) {
                $query->where('fecha_entrada', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('fecha_entrada', '<=', $request->fecha_hasta);
            }

            if ($request->has('habitacion_id')) {
                $query->where('habitacion_id', $request->habitacion_id);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 20);
            $reservas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'reservas' => $reservas->items(),
                'pagination' => [
                    'total' => $reservas->total(),
                    'currentPage' => $reservas->currentPage(),
                    'perPage' => $reservas->perPage(),
                    'lastPage' => $reservas->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar reservas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las reservas'
            ], 500);
        }
    }

    /**
     * Obtener una reserva específica
     */
    public function show($id)
    {
        try {
            $reserva = Reserva::with([
                'cliente',
                'habitacion.tipoHabitacion',
                'pago'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }
    }

    /**
     * Crear una nueva reserva
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cliente_cedula' => 'required|string|max:20',
                'cliente_nombre' => 'required|string|max:100',
                'cliente_apellido' => 'nullable|string|max:100',
                'cliente_email' => 'nullable|email|max:100',
                'cliente_telefono' => 'nullable|string|max:20',
                'habitacion_id' => 'required|exists:habitacions,id',
                'fecha_entrada' => 'required|date|after_or_equal:today',
                'fecha_salida' => 'required|date|after:fecha_entrada',
                'num_adultos' => 'required|integer|min:1',
                'num_ninos' => 'nullable|integer|min:0',
                'estado' => 'nullable|in:pendiente,pagada,en_curso,completada,cancelada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reserva = $this->reservaService->crearReserva($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente',
                'reserva' => $reserva
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Actualizar una reserva
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cliente_nombre' => 'sometimes|string|max:100',
                'cliente_apellido' => 'sometimes|string|max:100',
                'cliente_email' => 'sometimes|email|max:100',
                'cliente_telefono' => 'sometimes|string|max:20',
                'habitacion_id' => 'sometimes|exists:habitacions,id',
                'fecha_entrada' => 'sometimes|date',
                'fecha_salida' => 'sometimes|date|after:fecha_entrada',
                'num_adultos' => 'sometimes|integer|min:1',
                'num_ninos' => 'sometimes|integer|min:0',
                'estado' => 'sometimes|in:pendiente,pagada,en_curso,completada,cancelada'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reserva = $this->reservaService->actualizarReserva($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Reserva actualizada exitosamente',
                'reserva' => $reserva
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancelar una reserva
     */
    public function cancelar(Request $request, $id)
    {
        try {
            $motivo = $request->input('motivo');
            $reserva = $this->reservaService->cancelarReserva($id, $motivo);

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada exitosamente',
                'reserva' => $reserva
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cancelar reserva: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Realizar check-in
     */
    public function checkIn($id)
    {
        try {
            $reserva = $this->reservaService->checkIn($id);

            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado exitosamente',
                'reserva' => $reserva
            ]);
        } catch (\Exception $e) {
            Log::error('Error al hacer check-in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Realizar check-out
     */
    public function checkOut(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'consumos_adicionales' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $consumosAdicionales = $request->input('consumos_adicionales', 0);
            $reserva = $this->reservaService->checkOut($id, $consumosAdicionales);

            return response()->json([
                'success' => true,
                'message' => 'Check-out realizado exitosamente',
                'reserva' => $reserva
            ]);
        } catch (\Exception $e) {
            Log::error('Error al hacer check-out: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verificar disponibilidad
     */
    public function verificarDisponibilidad(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'habitacion_id' => 'required|exists:habitacions,id',
                'fecha_entrada' => 'required|date',
                'fecha_salida' => 'required|date|after:fecha_entrada',
                'reserva_id' => 'nullable|exists:reservas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $disponible = $this->reservaService->verificarDisponibilidad(
                $request->habitacion_id,
                $request->fecha_entrada,
                $request->fecha_salida,
                $request->reserva_id
            );

            return response()->json([
                'success' => true,
                'disponible' => $disponible
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar disponibilidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar disponibilidad'
            ], 500);
        }
    }

    /**
     * Buscar habitaciones disponibles
     */
    public function buscarDisponibles(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_entrada' => 'required|date',
                'fecha_salida' => 'required|date|after:fecha_entrada',
                'tipo_habitacion_id' => 'nullable|exists:tipo_habitacions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $habitaciones = $this->reservaService->buscarHabitacionesDisponibles(
                $request->fecha_entrada,
                $request->fecha_salida,
                $request->tipo_habitacion_id
            );

            return response()->json([
                'success' => true,
                'habitaciones' => $habitaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar habitaciones disponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar habitaciones'
            ], 500);
        }
    }

    /**
     * Obtener reservas del día
     */
    public function reservasDelDia()
    {
        try {
            $reservas = $this->reservaService->reservasDelDia();

            return response()->json([
                'success' => true,
                'data' => $reservas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener reservas del día: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reservas del día'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de reservas
     */
    public function estadisticas(Request $request)
    {
        try {
            $fechaInicio = $request->get('fecha_inicio');
            $fechaFin = $request->get('fecha_fin');

            $estadisticas = $this->reservaService->obtenerEstadisticas($fechaInicio, $fechaFin);

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas'
            ], 500);
        }
    }
}
