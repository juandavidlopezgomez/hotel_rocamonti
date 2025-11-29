<?php

namespace App\Http\Controllers;

use App\Models\TipoHabitacion;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('=== ROOM TYPES ENDPOINT LLAMADO ===');
            \Log::info('Parametros recibidos: ', $request->all());
            \Log::info('Headers: ', $request->headers->all());

            $tipos = TipoHabitacion::with('habitaciones')->get();

            \Log::info('Tipos de habitación encontrados: ' . $tipos->count());

            $resultado = $tipos->map(function($tipo) use ($request) {
                // Contar habitaciones disponibles (no ocupadas)
                $habitacionesDisponibles = $tipo->habitaciones->where('estado', 'disponible')->count();

                // Si hay fechas, verificar disponibilidad más específica
                if ($request->has('fecha_entrada') && $request->has('fecha_salida')) {
                    try {
                        $fechaEntrada = Carbon::parse($request->fecha_entrada);
                        $fechaSalida = Carbon::parse($request->fecha_salida);

                        // Obtener IDs de habitaciones de este tipo
                        $habitacionIds = $tipo->habitaciones->pluck('id');

                        // Contar cuántas están ocupadas en esas fechas
                        $habitacionesOcupadas = Reserva::whereIn('habitacion_id', $habitacionIds)
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

                        $habitacionesDisponibles = max(0, $habitacionesDisponibles - $habitacionesOcupadas);
                    } catch (\Exception $e) {
                        // Si hay error con fechas, usar disponibilidad base
                        \Log::error('Error al calcular disponibilidad: ' . $e->getMessage());
                    }
                }

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
                    'habitaciones_disponibles' => $habitacionesDisponibles
                ];
            });

            // Filtrar por tipo si se especifica
            if ($request->has('es_apartamento')) {
                $resultado = $resultado->filter(function($tipo) use ($request) {
                    return $tipo['es_apartamento'] == $request->boolean('es_apartamento');
                });
            }

            // Filtrar por capacidad si se especifica
            if ($request->has('num_adultos')) {
                $resultado = $resultado->filter(function($tipo) use ($request) {
                    return $tipo['capacidad_adultos'] >= $request->num_adultos;
                });
            }

            return response()->json([
                'success' => true,
                'tipos' => $resultado->values()
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', '*');

        } catch (\Exception $e) {
            \Log::error('Error en RoomController: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener habitaciones',
                'error' => $e->getMessage()
            ], 500)->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', '*');
        }
    }

    public function show($id)
    {
        try {
            $tipo = TipoHabitacion::with('habitaciones')->findOrFail($id);

            return response()->json([
                'success' => true,
                'tipo' => $tipo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de habitación no encontrado'
            ], 404);
        }
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'tipo_habitacion_id' => 'required|exists:tipo_habitacions,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada'
        ]);

        try {
            $tipo = TipoHabitacion::with('habitaciones')->findOrFail($request->tipo_habitacion_id);
            $fechaEntrada = Carbon::parse($request->fecha_entrada);
            $fechaSalida = Carbon::parse($request->fecha_salida);

            $habitacionIds = $tipo->habitaciones->pluck('id');

            $habitacionesOcupadas = Reserva::whereIn('habitacion_id', $habitacionIds)
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

            $disponibles = $tipo->habitaciones->count() - $habitacionesOcupadas;

            return response()->json([
                'success' => true,
                'disponibles' => max(0, $disponibles)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar disponibilidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada'
        ]);

        try {
            $fechaEntrada = Carbon::parse($request->fecha_entrada);
            $fechaSalida = Carbon::parse($request->fecha_salida);

            $tipos = TipoHabitacion::with('habitaciones')->get();

            $disponibles = $tipos->map(function($tipo) use ($fechaEntrada, $fechaSalida) {
                $habitacionIds = $tipo->habitaciones->pluck('id');

                $habitacionesOcupadas = Reserva::whereIn('habitacion_id', $habitacionIds)
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

                $disponibles = $tipo->habitaciones->count() - $habitacionesOcupadas;

                return [
                    'id' => $tipo->id,
                    'nombre' => $tipo->nombre,
                    'precio_base' => $tipo->precio_base,
                    'habitaciones_disponibles' => max(0, $disponibles)
                ];
            })->filter(function($tipo) {
                return $tipo['habitaciones_disponibles'] > 0;
            });

            return response()->json([
                'success' => true,
                'tipos' => $disponibles->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar habitaciones disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener fechas ocupadas para un tipo de habitación
     */
    public function getOccupiedDates($tipoHabitacionId)
    {
        try {
            // Obtener todas las habitaciones de este tipo
            $habitaciones = Habitacion::where('tipo_habitacion_id', $tipoHabitacionId)->get();
            $habitacionIds = $habitaciones->pluck('id');

            // Obtener todas las reservas activas para estas habitaciones
            $reservas = Reserva::whereIn('habitacion_id', $habitacionIds)
                ->where('estado', '!=', 'cancelada')
                ->where('fecha_salida', '>=', now()->format('Y-m-d'))
                ->get(['fecha_entrada', 'fecha_salida', 'habitacion_id']);

            // Agrupar por habitación para saber cuántas habitaciones están ocupadas por fecha
            $fechasOcupadas = [];
            $totalHabitaciones = $habitaciones->count();

            foreach ($reservas as $reserva) {
                $fechaActual = new \DateTime($reserva->fecha_entrada);
                $fechaFin = new \DateTime($reserva->fecha_salida);

                while ($fechaActual < $fechaFin) {
                    $fecha = $fechaActual->format('Y-m-d');

                    if (!isset($fechasOcupadas[$fecha])) {
                        $fechasOcupadas[$fecha] = 0;
                    }

                    $fechasOcupadas[$fecha]++;
                    $fechaActual->modify('+1 day');
                }
            }

            // Marcar fechas como completamente ocupadas o parcialmente ocupadas
            $resultado = [];
            foreach ($fechasOcupadas as $fecha => $numOcupadas) {
                $resultado[] = [
                    'fecha' => $fecha,
                    'ocupadas' => $numOcupadas,
                    'disponibles' => $totalHabitaciones - $numOcupadas,
                    'completamente_ocupada' => $numOcupadas >= $totalHabitaciones
                ];
            }

            return response()->json([
                'success' => true,
                'fechas' => $resultado,
                'total_habitaciones' => $totalHabitaciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener fechas ocupadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
