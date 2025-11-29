<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generar reporte de reservas
     */
    public function generarReporteReservas($fechaInicio, $fechaFin, $filtros = [])
    {
        $query = Reserva::with(['cliente', 'habitacion.tipoHabitacion'])
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin]);

        // Aplicar filtros
        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['habitacion_id'])) {
            $query->where('habitacion_id', $filtros['habitacion_id']);
        }

        $reservas = $query->orderBy('fecha_entrada', 'desc')->get();

        return [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ],
            'resumen' => [
                'total_reservas' => $reservas->count(),
                'reservas_por_estado' => [
                    'pendiente' => $reservas->where('estado', 'pendiente')->count(),
                    'pagada' => $reservas->where('estado', 'pagada')->count(),
                    'en_curso' => $reservas->where('estado', 'en_curso')->count(),
                    'completada' => $reservas->where('estado', 'completada')->count(),
                    'cancelada' => $reservas->where('estado', 'cancelada')->count(),
                ],
                'ingresos_totales' => $reservas->whereIn('estado', ['pagada', 'completada'])->sum('precio_total'),
                'ingresos_pendientes' => $reservas->where('estado', 'pendiente')->sum('precio_total'),
                'promedio_por_reserva' => $reservas->count() > 0 ? round($reservas->sum('precio_total') / $reservas->count(), 2) : 0,
            ],
            'reservas' => $reservas,
        ];
    }

    /**
     * Generar reporte de ingresos
     */
    public function generarReporteIngresos($fechaInicio, $fechaFin, $agruparPor = 'dia')
    {
        $reservas = Reserva::with(['habitacion.tipoHabitacion'])
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['pagada', 'completada'])
            ->get();

        // Agrupar ingresos
        $ingresosPorPeriodo = [];
        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinal = Carbon::parse($fechaFin);

        while ($fechaActual->lessThanOrEqualTo($fechaFinal)) {
            $formato = $agruparPor === 'mes' ? 'Y-m' : 'Y-m-d';
            $periodo = $fechaActual->format($formato);

            if (!isset($ingresosPorPeriodo[$periodo])) {
                $ingresosPorPeriodo[$periodo] = [
                    'periodo' => $periodo,
                    'total' => 0,
                    'cantidad_reservas' => 0,
                    'promedio' => 0,
                ];
            }

            if ($agruparPor === 'mes') {
                $fechaActual->addMonth();
            } else {
                $fechaActual->addDay();
            }
        }

        // Sumar ingresos
        foreach ($reservas as $reserva) {
            $formato = $agruparPor === 'mes' ? 'Y-m' : 'Y-m-d';
            $periodo = Carbon::parse($reserva->fecha_entrada)->format($formato);

            if (isset($ingresosPorPeriodo[$periodo])) {
                $ingresosPorPeriodo[$periodo]['total'] += $reserva->precio_total;
                $ingresosPorPeriodo[$periodo]['cantidad_reservas']++;
            }
        }

        // Calcular promedios
        foreach ($ingresosPorPeriodo as &$periodo) {
            if ($periodo['cantidad_reservas'] > 0) {
                $periodo['promedio'] = round($periodo['total'] / $periodo['cantidad_reservas'], 2);
            }
        }

        // Ingresos por tipo de habitación
        $ingresosPorTipo = $reservas->groupBy(function ($reserva) {
            return $reserva->habitacion->tipoHabitacion->nombre;
        })->map(function ($grupo, $tipo) {
            return [
                'tipo' => $tipo,
                'total' => $grupo->sum('precio_total'),
                'cantidad' => $grupo->count(),
                'promedio' => round($grupo->sum('precio_total') / $grupo->count(), 2),
            ];
        })->values();

        return [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ],
            'resumen' => [
                'total_ingresos' => $reservas->sum('precio_total'),
                'cantidad_reservas' => $reservas->count(),
                'promedio_por_reserva' => $reservas->count() > 0 ? round($reservas->sum('precio_total') / $reservas->count(), 2) : 0,
            ],
            'ingresos_por_periodo' => array_values($ingresosPorPeriodo),
            'ingresos_por_tipo_habitacion' => $ingresosPorTipo,
        ];
    }

    /**
     * Generar reporte de ocupación
     */
    public function generarReporteOcupacion($fechaInicio, $fechaFin)
    {
        $totalHabitaciones = Habitacion::count();

        if ($totalHabitaciones === 0) {
            throw new \Exception('No hay habitaciones registradas en el sistema');
        }

        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinal = Carbon::parse($fechaFin);
        $diasTotales = $fechaActual->diffInDays($fechaFinal) + 1;

        // Habitaciones-noche disponibles
        $habitacionesNocheDisponibles = $totalHabitaciones * $diasTotales;

        // Habitaciones-noche ocupadas
        $habitacionesNocheOcupadas = Reserva::where(function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                  ->orWhereBetween('fecha_salida', [$fechaInicio, $fechaFin])
                  ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                      $q->where('fecha_entrada', '<=', $fechaInicio)
                        ->where('fecha_salida', '>=', $fechaFin);
                  });
        })
        ->whereIn('estado', ['pagada', 'en_curso', 'completada'])
        ->get()
        ->sum(function ($reserva) use ($fechaInicio, $fechaFin) {
            $entrada = max(Carbon::parse($reserva->fecha_entrada), Carbon::parse($fechaInicio));
            $salida = min(Carbon::parse($reserva->fecha_salida), Carbon::parse($fechaFin));
            return $entrada->diffInDays($salida);
        });

        // Calcular tasa de ocupación
        $tasaOcupacion = $habitacionesNocheDisponibles > 0
            ? round(($habitacionesNocheOcupadas / $habitacionesNocheDisponibles) * 100, 2)
            : 0;

        // Ocupación por tipo de habitación
        $ocupacionPorTipo = DB::table('reservas')
            ->join('habitacions', 'reservas.habitacion_id', '=', 'habitacions.id')
            ->join('tipo_habitacions', 'habitacions.tipo_habitacion_id', '=', 'tipo_habitacions.id')
            ->whereBetween('reservas.fecha_entrada', [$fechaInicio, $fechaFin])
            ->whereIn('reservas.estado', ['pagada', 'en_curso', 'completada'])
            ->select(
                'tipo_habitacions.nombre',
                DB::raw('COUNT(DISTINCT reservas.id) as total_reservas'),
                DB::raw('SUM(DATEDIFF(reservas.fecha_salida, reservas.fecha_entrada)) as noches_ocupadas')
            )
            ->groupBy('tipo_habitacions.id', 'tipo_habitacions.nombre')
            ->get();

        // Ocupación por día
        $ocupacionPorDia = [];
        $fechaActual = Carbon::parse($fechaInicio);

        while ($fechaActual->lessThanOrEqualTo($fechaFinal)) {
            $fecha = $fechaActual->format('Y-m-d');

            $habitacionesOcupadas = Reserva::where('fecha_entrada', '<=', $fecha)
                ->where('fecha_salida', '>', $fecha)
                ->whereIn('estado', ['pagada', 'en_curso'])
                ->count();

            $ocupacionPorDia[] = [
                'fecha' => $fecha,
                'habitaciones_ocupadas' => $habitacionesOcupadas,
                'habitaciones_disponibles' => $totalHabitaciones - $habitacionesOcupadas,
                'tasa_ocupacion' => round(($habitacionesOcupadas / $totalHabitaciones) * 100, 2),
            ];

            $fechaActual->addDay();
        }

        // Calcular RevPAR (Revenue Per Available Room)
        $ingresosTotales = Reserva::whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['pagada', 'completada'])
            ->sum('precio_total');

        $revpar = $habitacionesNocheDisponibles > 0
            ? round($ingresosTotales / $habitacionesNocheDisponibles, 2)
            : 0;

        return [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
                'dias' => $diasTotales
            ],
            'resumen' => [
                'total_habitaciones' => $totalHabitaciones,
                'habitaciones_noche_disponibles' => $habitacionesNocheDisponibles,
                'habitaciones_noche_ocupadas' => $habitacionesNocheOcupadas,
                'tasa_ocupacion' => $tasaOcupacion,
                'revpar' => $revpar,
            ],
            'ocupacion_por_tipo' => $ocupacionPorTipo,
            'ocupacion_por_dia' => $ocupacionPorDia,
        ];
    }

    /**
     * Generar reporte de clientes
     */
    public function generarReporteClientes($fechaInicio = null, $fechaFin = null)
    {
        $query = Cliente::with(['reservas' => function ($q) use ($fechaInicio, $fechaFin) {
            if ($fechaInicio && $fechaFin) {
                $q->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin]);
            }
        }]);

        $clientes = $query->get();

        // Calcular estadísticas de cada cliente
        $clientesConEstadisticas = $clientes->map(function ($cliente) {
            $reservas = $cliente->reservas;
            $totalGastado = $reservas->whereIn('estado', ['pagada', 'completada'])->sum('precio_total');
            $totalReservas = $reservas->count();

            return [
                'cedula' => $cliente->cedula,
                'nombre' => $cliente->nombre,
                'apellido' => $cliente->apellido,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
                'total_reservas' => $totalReservas,
                'total_gastado' => $totalGastado,
                'es_vip' => $totalReservas >= 5 || $totalGastado >= 5000000,
                'promedio_por_reserva' => $totalReservas > 0 ? round($totalGastado / $totalReservas, 2) : 0,
                'ultima_reserva' => $reservas->sortByDesc('created_at')->first()?->created_at,
            ];
        });

        // Ordenar por total gastado
        $topClientes = $clientesConEstadisticas->sortByDesc('total_gastado')->take(10)->values();

        return [
            'periodo' => $fechaInicio && $fechaFin ? [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ] : null,
            'resumen' => [
                'total_clientes' => $clientes->count(),
                'clientes_vip' => $clientesConEstadisticas->where('es_vip', true)->count(),
                'clientes_nuevos' => $clientes->where('created_at', '>=', Carbon::now()->subMonth())->count(),
                'total_gastado_general' => $clientesConEstadisticas->sum('total_gastado'),
            ],
            'top_clientes' => $topClientes,
            'segmentacion' => [
                'por_frecuencia' => [
                    '1_reserva' => $clientesConEstadisticas->where('total_reservas', 1)->count(),
                    '2_4_reservas' => $clientesConEstadisticas->whereBetween('total_reservas', [2, 4])->count(),
                    '5_9_reservas' => $clientesConEstadisticas->whereBetween('total_reservas', [5, 9])->count(),
                    '10_mas_reservas' => $clientesConEstadisticas->where('total_reservas', '>=', 10)->count(),
                ],
                'por_gasto' => [
                    'menos_1M' => $clientesConEstadisticas->where('total_gastado', '<', 1000000)->count(),
                    '1M_5M' => $clientesConEstadisticas->whereBetween('total_gastado', [1000000, 5000000])->count(),
                    'mas_5M' => $clientesConEstadisticas->where('total_gastado', '>', 5000000)->count(),
                ],
            ],
        ];
    }

    /**
     * Obtener estadísticas generales del dashboard
     */
    public function obtenerEstadisticasDashboard()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();

        // Ingresos del mes
        $ingresosMes = Reserva::whereBetween('fecha_entrada', [$inicioMes, $finMes])
            ->whereIn('estado', ['pagada', 'completada'])
            ->sum('precio_total');

        $ingresosMesAnterior = Reserva::whereBetween('fecha_entrada', [$inicioMesAnterior, $finMesAnterior])
            ->whereIn('estado', ['pagada', 'completada'])
            ->sum('precio_total');

        // Reservas activas
        $reservasActivas = Reserva::whereIn('estado', ['pagada', 'en_curso'])
            ->count();

        // Ocupación
        $totalHabitaciones = Habitacion::count();
        $habitacionesOcupadas = Reserva::where('fecha_entrada', '<=', $hoy)
            ->where('fecha_salida', '>', $hoy)
            ->whereIn('estado', ['pagada', 'en_curso'])
            ->count();

        $tasaOcupacion = $totalHabitaciones > 0
            ? round(($habitacionesOcupadas / $totalHabitaciones) * 100, 2)
            : 0;

        // Llegadas y salidas hoy
        $llegadasHoy = Reserva::whereDate('fecha_entrada', $hoy)
            ->where('estado', '!=', 'cancelada')
            ->count();

        $salidasHoy = Reserva::whereDate('fecha_salida', $hoy)
            ->where('estado', '!=', 'cancelada')
            ->where('estado', '!=', 'completada')
            ->count();

        return [
            'ingresos_mes' => $ingresosMes,
            'ingresos_mes_anterior' => $ingresosMesAnterior,
            'reservas_activas' => $reservasActivas,
            'tasa_ocupacion' => $tasaOcupacion,
            'habitaciones_ocupadas' => $habitacionesOcupadas,
            'total_habitaciones' => $totalHabitaciones,
            'llegadas_hoy' => $llegadasHoy,
            'salidas_hoy' => $salidasHoy,
        ];
    }
}
