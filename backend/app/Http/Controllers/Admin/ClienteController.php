<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    /**
     * Listar clientes con búsqueda y filtros
     */
    public function index(Request $request)
    {
        try {
            $busqueda = $request->input('busqueda', '');
            $vipOnly = $request->input('vip_only', false);

            $query = Cliente::withCount('reservas');

            // Filtro de búsqueda
            if ($busqueda) {
                $query->where(function($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('apellido', 'like', "%{$busqueda}%")
                      ->orWhere('cedula', 'like', "%{$busqueda}%")
                      ->orWhere('email', 'like', "%{$busqueda}%")
                      ->orWhere('telefono', 'like', "%{$busqueda}%");
                });
            }

            $clientes = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function($cliente) {
                    $totalGastado = Reserva::where('cliente_cedula', $cliente->cedula)
                        ->where('estado', 'pagada')
                        ->sum('precio_total');

                    $ultimaReserva = Reserva::where('cliente_cedula', $cliente->cedula)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    // Calcular estatus VIP
                    $vipStatus = $this->calcularVipStatus($cliente->reservas_count, $totalGastado);

                    return [
                        'id' => $cliente->cedula,
                        'cedula' => $cliente->cedula,
                        'nombre' => $cliente->nombre,
                        'apellido' => $cliente->apellido,
                        'email' => $cliente->email,
                        'telefono' => $cliente->telefono,
                        'num_reservas' => $cliente->reservas_count,
                        'total_gastado' => $totalGastado,
                        'ultima_reserva' => $ultimaReserva ? $ultimaReserva->fecha_entrada : null,
                        'vip_status' => $vipStatus
                    ];
                });

            // Filtrar solo VIP si se solicita
            if ($vipOnly) {
                $clientes = $clientes->filter(function($cliente) {
                    return $cliente['vip_status']['es_vip'];
                })->values();
            }

            return response()->json([
                'success' => true,
                'clientes' => $clientes
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar clientes'
            ], 500);
        }
    }

    /**
     * Ver perfil completo de cliente con historial
     */
    public function show($cedula)
    {
        try {
            $cliente = Cliente::where('cedula', $cedula)
                ->withCount('reservas')
                ->firstOrFail();

            $totalGastado = Reserva::where('cliente_cedula', $cedula)
                ->where('estado', 'pagada')
                ->sum('precio_total');

            $reservas = Reserva::where('cliente_cedula', $cedula)
                ->with(['habitacion.tipoHabitacion'])
                ->orderBy('created_at', 'desc')
                ->get();

            $vipStatus = $this->calcularVipStatus($cliente->reservas_count, $totalGastado);

            return response()->json([
                'success' => true,
                'cliente' => [
                    'cedula' => $cliente->cedula,
                    'nombre' => $cliente->nombre,
                    'apellido' => $cliente->apellido,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'num_reservas' => $cliente->reservas_count,
                    'total_gastado' => $totalGastado,
                    'vip_status' => $vipStatus,
                    'reservas' => $reservas
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar datos de cliente
     */
    public function update(Request $request, $cedula)
    {
        try {
            $cliente = Cliente::where('cedula', $cedula)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:100',
                'apellido' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|max:100',
                'telefono' => 'sometimes|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cliente->update($request->only(['nombre', 'apellido', 'email', 'telefono']));

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'cliente' => $cliente
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente'
            ], 500);
        }
    }

    /**
     * Eliminar cliente
     */
    public function destroy($cedula)
    {
        try {
            $cliente = Cliente::where('cedula', $cedula)->firstOrFail();

            // Verificar que no tenga reservas activas o futuras
            $reservasActivas = Reserva::where('cliente_cedula', $cedula)
                ->where('fecha_salida', '>=', now())
                ->whereIn('estado', ['pagada', 'pendiente', 'en_curso'])
                ->exists();

            if ($reservasActivas) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar. El cliente tiene reservas activas o futuras.'
                ], 400);
            }

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente'
            ], 500);
        }
    }

    /**
     * Obtener estatus VIP de cliente
     */
    public function getVipStatus($cedula)
    {
        try {
            $cliente = Cliente::where('cedula', $cedula)
                ->withCount('reservas')
                ->firstOrFail();

            $totalGastado = Reserva::where('cliente_cedula', $cedula)
                ->where('estado', 'pagada')
                ->sum('precio_total');

            $vipStatus = $this->calcularVipStatus($cliente->reservas_count, $totalGastado);

            return response()->json([
                'success' => true,
                'vip_status' => $vipStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estatus VIP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular estatus VIP'
            ], 500);
        }
    }

    /**
     * Obtener historial de reservas
     */
    public function getHistorialReservas($cedula)
    {
        try {
            $reservas = Reserva::where('cliente_cedula', $cedula)
                ->with(['habitacion.tipoHabitacion', 'pago'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($reserva) {
                    return [
                        'id' => $reserva->id,
                        'fecha_reserva' => $reserva->created_at->format('d/m/Y H:i'),
                        'fecha_entrada' => $reserva->fecha_entrada,
                        'fecha_salida' => $reserva->fecha_salida,
                        'habitacion' => $reserva->habitacion ? $reserva->habitacion->numero_habitacion : 'N/A',
                        'tipo' => $reserva->habitacion && $reserva->habitacion->tipoHabitacion
                            ? $reserva->habitacion->tipoHabitacion->nombre
                            : 'N/A',
                        'estado' => $reserva->estado,
                        'precio_total' => $reserva->precio_total,
                        'num_adultos' => $reserva->num_adultos,
                        'num_ninos' => $reserva->num_ninos
                    ];
                });

            return response()->json([
                'success' => true,
                'reservas' => $reservas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historial: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar historial'
            ], 500);
        }
    }

    /**
     * Obtener comunicaciones del cliente
     */
    public function getComunicaciones($cedula)
    {
        try {
            // Por ahora retornamos array vacío
            // En implementación completa habría tabla de comunicaciones
            return response()->json([
                'success' => true,
                'comunicaciones' => []
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener comunicaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar comunicaciones'
            ], 500);
        }
    }

    /**
     * Estadísticas de clientes
     */
    public function stats()
    {
        try {
            $total = Cliente::count();
            $conReservas = Cliente::has('reservas')->count();

            $ingresosTotales = Reserva::where('estado', 'pagada')->sum('precio_total');

            $clienteVIP = Cliente::withCount('reservas')
                ->orderBy('reservas_count', 'desc')
                ->first();

            // Clientes VIP
            $clientesVIP = Cliente::withCount('reservas')
                ->get()
                ->filter(function($cliente) {
                    $totalGastado = Reserva::where('cliente_cedula', $cliente->cedula)
                        ->where('estado', 'pagada')
                        ->sum('precio_total');
                    $vipStatus = $this->calcularVipStatus($cliente->reservas_count, $totalGastado);
                    return $vipStatus['es_vip'];
                })
                ->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total' => $total,
                    'con_reservas' => $conReservas,
                    'clientes_vip' => $clientesVIP,
                    'ingresos_totales' => $ingresosTotales,
                    'cliente_top' => $clienteVIP ? [
                        'nombre' => $clienteVIP->nombre . ' ' . $clienteVIP->apellido,
                        'reservas' => $clienteVIP->reservas_count
                    ] : null
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
     * Método helper para calcular estatus VIP
     */
    private function calcularVipStatus($numReservas, $totalGastado)
    {
        $esVip = false;
        $nivel = 'Regular';
        $beneficios = [];

        if ($numReservas >= 10 || $totalGastado >= 5000000) {
            $esVip = true;
            $nivel = 'Platinum';
            $beneficios = ['20% descuento', 'Upgrade gratis', 'Early check-in', 'Late check-out'];
        } elseif ($numReservas >= 5 || $totalGastado >= 2000000) {
            $esVip = true;
            $nivel = 'Gold';
            $beneficios = ['15% descuento', 'Late check-out', 'Desayuno incluido'];
        } elseif ($numReservas >= 3 || $totalGastado >= 1000000) {
            $esVip = true;
            $nivel = 'Silver';
            $beneficios = ['10% descuento', 'Desayuno incluido'];
        }

        return [
            'es_vip' => $esVip,
            'nivel' => $nivel,
            'num_reservas' => $numReservas,
            'total_gastado' => $totalGastado,
            'beneficios' => $beneficios,
            'progreso_siguiente_nivel' => $this->calcularProgresoNivel($numReservas, $totalGastado, $nivel)
        ];
    }

    /**
     * Calcular progreso hacia siguiente nivel VIP
     */
    private function calcularProgresoNivel($numReservas, $totalGastado, $nivelActual)
    {
        if ($nivelActual === 'Platinum') {
            return ['progreso' => 100, 'siguiente_nivel' => null];
        }

        $objetivos = [
            'Regular' => ['reservas' => 3, 'monto' => 1000000, 'siguiente' => 'Silver'],
            'Silver' => ['reservas' => 5, 'monto' => 2000000, 'siguiente' => 'Gold'],
            'Gold' => ['reservas' => 10, 'monto' => 5000000, 'siguiente' => 'Platinum']
        ];

        $objetivo = $objetivos[$nivelActual] ?? null;
        if (!$objetivo) return ['progreso' => 0, 'siguiente_nivel' => null];

        $progresoReservas = min(100, ($numReservas / $objetivo['reservas']) * 100);
        $progresoMonto = min(100, ($totalGastado / $objetivo['monto']) * 100);
        $progreso = max($progresoReservas, $progresoMonto);

        return [
            'progreso' => round($progreso, 2),
            'siguiente_nivel' => $objetivo['siguiente'],
            'reservas_faltantes' => max(0, $objetivo['reservas'] - $numReservas),
            'monto_faltante' => max(0, $objetivo['monto'] - $totalGastado)
        ];
    }

    /**
     * Método legacy - mantener compatibilidad
     */
    public function historial($cedula)
    {
        return $this->getHistorialReservas($cedula);
    }
}
