<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalReservas = Reserva::count();
        $habitacionesOcupadas = Reserva::where('estado', 'pagada')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reservas' => $totalReservas,
                'habitaciones_ocupadas' => $habitacionesOcupadas,
            ]
        ]);
    }

    public function stats()
    {
        $totalHabitaciones = TipoHabitacion::count();
        $totalReservas = Reserva::count();
        $reservasMes = Reserva::whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->count();

        $ingresosTotales = Reserva::where('estado', 'pagada')->sum('precio_total');
        $ingresosMes = Reserva::where('estado', 'pagada')
                             ->whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year)
                             ->sum('precio_total');

        // Calcular habitaciones ocupadas y tasa de ocupación
        $ocupadas = Reserva::where('estado', 'activa')
                          ->where('fecha_salida', '>=', now())
                          ->where('fecha_entrada', '<=', now())
                          ->distinct('habitacion_id')
                          ->count('habitacion_id');

        $totalHabitacionesCount = Habitacion::count();
        $tasaOcupacion = $totalHabitacionesCount > 0 ? round(($ocupadas / $totalHabitacionesCount) * 100, 1) : 0;

        // Calcular ingresos del mes anterior para comparación
        $ingresosMesAnterior = Reserva::where('estado', 'pagada')
                             ->whereMonth('created_at', now()->subMonth()->month)
                             ->whereYear('created_at', now()->subMonth()->year)
                             ->sum('precio_total');

        // Llegadas y salidas de hoy
        $llegadasHoy = Reserva::whereDate('fecha_entrada', now())->count();
        $salidasHoy = Reserva::whereDate('fecha_salida', now())->count();

        // Reservas activas (confirmadas o en curso)
        $reservasActivas = Reserva::whereIn('estado', ['confirmada', 'activa'])->count();
        $reservasEstaSemana = Reserva::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalHabitaciones' => $totalHabitacionesCount,
                'disponibles' => max(0, $totalHabitacionesCount - $ocupadas),
                'habitacionesOcupadas' => $ocupadas,
                'totalReservas' => $totalReservas,
                'reservasMes' => $reservasMes,
                'ingresosTotales' => $ingresosTotales,
                'ingresosMes' => floatval($ingresosMes),
                'ingresosMesAnterior' => floatval($ingresosMesAnterior > 0 ? $ingresosMesAnterior : 1),
                'tasaOcupacion' => $tasaOcupacion,
                'llegadasHoy' => $llegadasHoy,
                'salidasHoy' => $salidasHoy,
                'reservasActivas' => $reservasActivas,
                'reservasEstaSemana' => $reservasEstaSemana
            ]
        ]);
    }

    public function proximasReservas(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->addDays(7)->toDateString());

        $reservas = Reserva::with(['cliente', 'tipoHabitacion'])
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['pagada', 'pendiente'])
            ->orderBy('fecha_entrada', 'asc')
            ->get()
            ->map(function($reserva) {
                return [
                    'id' => $reserva->id,
                    'cliente_nombre' => $reserva->cliente->nombre . ' ' . $reserva->cliente->apellido,
                    'tipo_nombre' => $reserva->tipoHabitacion->nombre,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'estado' => $reserva->estado,
                    'precio_total' => $reserva->precio_total
                ];
            });

        return response()->json([
            'success' => true,
            'reservas' => $reservas
        ]);
    }

    public function ocupacionSemanal()
    {
        $datos = [];
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $reservas = Reserva::whereDate('fecha_entrada', '<=', $fecha)
                              ->whereDate('fecha_salida', '>=', $fecha)
                              ->whereIn('estado', ['pagada', 'pendiente'])
                              ->count();

            $datos[] = [
                'dia' => $dias[$fecha->dayOfWeek],
                'reservas' => $reservas,
                'fecha' => $fecha->toDateString(),
                'porcentaje' => min(100, ($reservas / max(1, TipoHabitacion::count())) * 100)
            ];
        }

        return response()->json([
            'success' => true,
            'datos' => $datos
        ]);
    }

    public function topHabitaciones(Request $request)
    {
        $limit = $request->input('limit', 5);

        // Contar reservas por tipo de habitación usando subconsulta
        $tipos = DB::table('tipo_habitacions as t')
            ->select('t.id', 't.nombre')
            ->selectRaw('COUNT(r.id) as total')
            ->leftJoin('habitacions as h', 't.id', '=', 'h.tipo_habitacion_id')
            ->leftJoin('reservas as r', function($join) {
                $join->on('h.id', '=', 'r.habitacion_id')
                     ->whereIn('r.estado', ['confirmada', 'activa', 'pagada']);
            })
            ->groupBy('t.id', 't.nombre')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();

        // Encontrar el máximo para calcular porcentajes
        $maxReservas = $tipos->max('total');

        $tiposFormateados = $tipos->map(function($tipo) use ($maxReservas) {
            return [
                'id' => $tipo->id,
                'nombre' => $tipo->nombre,
                'total' => $tipo->total,
                'porcentaje' => $maxReservas > 0 ? round(($tipo->total / $maxReservas) * 100, 1) : 0
            ];
        });

        return response()->json([
            'success' => true,
            'tipos' => $tiposFormateados
        ]);
    }

    public function generarReporte(Request $request)
    {
        $tipo = $request->input('tipo', 'reservas');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if (!$fechaInicio || !$fechaFin) {
            return response()->json([
                'success' => false,
                'error' => 'Las fechas de inicio y fin son requeridas'
            ], 400);
        }

        switch ($tipo) {
            case 'reservas':
                return $this->reporteReservas($fechaInicio, $fechaFin);
            case 'ingresos':
                return $this->reporteIngresos($fechaInicio, $fechaFin);
            case 'ocupacion':
                return $this->reporteOcupacion($fechaInicio, $fechaFin);
            case 'clientes':
                return $this->reporteClientesFrecuentes($fechaInicio, $fechaFin);
            default:
                return response()->json([
                    'success' => false,
                    'error' => 'Tipo de reporte no válido'
                ], 400);
        }
    }

    private function reporteReservas($fechaInicio, $fechaFin)
    {
        $reservas = Reserva::with(['cliente', 'tipoHabitacion'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();

        $datos = $reservas->map(function($r) {
            return [
                'id' => $r->id,
                'fecha' => $r->created_at->format('d/m/Y'),
                'cliente' => $r->cliente->nombre . ' ' . $r->cliente->apellido,
                'habitacion' => $r->tipoHabitacion->nombre,
                'entrada' => Carbon::parse($r->fecha_entrada)->format('d/m/Y'),
                'salida' => Carbon::parse($r->fecha_salida)->format('d/m/Y'),
                'estado' => ucfirst($r->estado),
                'total' => $r->precio_total
            ];
        });

        return response()->json([
            'success' => true,
            'reporte' => [
                'titulo' => 'Reporte de Reservas',
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'resumen' => [
                    'total' => [
                        'label' => 'Total Reservas',
                        'value' => $reservas->count()
                    ],
                    'pagadas' => [
                        'label' => 'Pagadas',
                        'value' => $reservas->where('estado', 'pagada')->count()
                    ],
                    'pendientes' => [
                        'label' => 'Pendientes',
                        'value' => $reservas->where('estado', 'pendiente')->count()
                    ]
                ],
                'columnas' => [
                    ['key' => 'id', 'label' => 'ID'],
                    ['key' => 'fecha', 'label' => 'Fecha'],
                    ['key' => 'cliente', 'label' => 'Cliente'],
                    ['key' => 'habitacion', 'label' => 'Habitación'],
                    ['key' => 'entrada', 'label' => 'Entrada'],
                    ['key' => 'salida', 'label' => 'Salida'],
                    ['key' => 'estado', 'label' => 'Estado'],
                    ['key' => 'total', 'label' => 'Total', 'type' => 'precio']
                ],
                'datos' => $datos
            ]
        ]);
    }

    private function reporteIngresos($fechaInicio, $fechaFin)
    {
        $reservas = Reserva::where('estado', 'pagada')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->with('tipoHabitacion')
            ->get();

        $ingresosPorTipo = $reservas->groupBy('tipo_habitacion_id')
            ->map(function($grupo) {
                $tipo = $grupo->first()->tipoHabitacion;
                return [
                    'tipo' => $tipo->nombre,
                    'cantidad' => $grupo->count(),
                    'ingresos' => $grupo->sum('precio_total')
                ];
            })->values();

        $totalIngresos = $reservas->sum('precio_total');

        return response()->json([
            'success' => true,
            'reporte' => [
                'titulo' => 'Reporte de Ingresos',
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'resumen' => [
                    'total' => [
                        'label' => 'Ingresos Totales',
                        'value' => '$' . number_format($totalIngresos, 0, ',', '.')
                    ],
                    'reservas' => [
                        'label' => 'Reservas Pagadas',
                        'value' => $reservas->count()
                    ],
                    'promedio' => [
                        'label' => 'Ingreso Promedio',
                        'value' => '$' . number_format($reservas->count() > 0 ? $totalIngresos / $reservas->count() : 0, 0, ',', '.')
                    ]
                ],
                'columnas' => [
                    ['key' => 'tipo', 'label' => 'Tipo Habitación'],
                    ['key' => 'cantidad', 'label' => 'Cantidad'],
                    ['key' => 'ingresos', 'label' => 'Ingresos', 'type' => 'precio']
                ],
                'datos' => $ingresosPorTipo
            ]
        ]);
    }

    private function reporteOcupacion($fechaInicio, $fechaFin)
    {
        $datos = [];
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);

        while ($inicio <= $fin) {
            $reservas = Reserva::whereDate('fecha_entrada', '<=', $inicio)
                              ->whereDate('fecha_salida', '>=', $inicio)
                              ->whereIn('estado', ['pagada', 'pendiente'])
                              ->count();

            $datos[] = [
                'fecha' => $inicio->format('d/m/Y'),
                'reservas' => $reservas,
                'porcentaje' => round(($reservas / max(1, TipoHabitacion::count())) * 100, 1) . '%'
            ];

            $inicio->addDay();
        }

        return response()->json([
            'success' => true,
            'reporte' => [
                'titulo' => 'Reporte de Ocupación',
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'resumen' => [
                    'promedio' => [
                        'label' => 'Ocupación Promedio',
                        'value' => round(collect($datos)->avg(function($d) {
                            return (float)str_replace('%', '', $d['porcentaje']);
                        }), 1) . '%'
                    ]
                ],
                'columnas' => [
                    ['key' => 'fecha', 'label' => 'Fecha'],
                    ['key' => 'reservas', 'label' => 'Habitaciones Ocupadas'],
                    ['key' => 'porcentaje', 'label' => 'Porcentaje']
                ],
                'datos' => $datos
            ]
        ]);
    }

    private function reporteClientesFrecuentes($fechaInicio, $fechaFin)
    {
        $clientes = Cliente::withCount(['reservas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            }])
            ->having('reservas_count', '>', 0)
            ->orderBy('reservas_count', 'desc')
            ->get()
            ->map(function($cliente) {
                return [
                    'nombre' => $cliente->nombre . ' ' . $cliente->apellido,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'reservas' => $cliente->reservas_count
                ];
            });

        return response()->json([
            'success' => true,
            'reporte' => [
                'titulo' => 'Clientes Frecuentes',
                'periodo' => Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($fechaFin)->format('d/m/Y'),
                'resumen' => [
                    'total' => [
                        'label' => 'Total Clientes',
                        'value' => $clientes->count()
                    ]
                ],
                'columnas' => [
                    ['key' => 'nombre', 'label' => 'Cliente'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'telefono', 'label' => 'Teléfono'],
                    ['key' => 'reservas', 'label' => 'Reservas']
                ],
                'datos' => $clientes
            ]
        ]);
    }
}
