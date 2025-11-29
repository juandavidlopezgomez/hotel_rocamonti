<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Reporte de reservas por fecha y estado
     */
    public function reporteReservas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());
            $estado = $request->input('estado');

            $query = Reserva::with(['cliente', 'habitacion.tipoHabitacion'])
                ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin]);

            if ($estado) {
                $query->where('estado', $estado);
            }

            $reservas = $query->orderBy('fecha_entrada', 'desc')->get();

            $resumen = [
                'total_reservas' => $reservas->count(),
                'por_estado' => [
                    'pendiente' => $reservas->where('estado', 'pendiente')->count(),
                    'pagada' => $reservas->where('estado', 'pagada')->count(),
                    'en_curso' => $reservas->where('estado', 'en_curso')->count(),
                    'completada' => $reservas->where('estado', 'completada')->count(),
                    'cancelada' => $reservas->where('estado', 'cancelada')->count(),
                ],
                'ingresos_totales' => $reservas->where('estado', 'pagada')->sum('precio_total'),
                'ingresos_pendientes' => $reservas->where('estado', 'pendiente')->sum('precio_total'),
            ];

            return response()->json([
                'success' => true,
                'reservas' => $reservas,
                'resumen' => $resumen,
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte de reservas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de reservas'
            ], 500);
        }
    }

    /**
     * Reporte de ingresos con comparativas
     */
    public function reporteIngresos(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());

            // Ingresos del periodo actual
            $ingresosPeriodo = Reserva::where('estado', 'pagada')
                ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                ->sum('precio_total');

            // Periodo anterior (mismo rango de tiempo)
            $diasDiferencia = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin));
            $fechaInicioAnterior = Carbon::parse($fechaInicio)->subDays($diasDiferencia + 1);
            $fechaFinAnterior = Carbon::parse($fechaInicio)->subDay();

            $ingresosPeriodoAnterior = Reserva::where('estado', 'pagada')
                ->whereBetween('fecha_entrada', [$fechaInicioAnterior, $fechaFinAnterior])
                ->sum('precio_total');

            // Calcular variación porcentual
            $variacion = 0;
            if ($ingresosPeriodoAnterior > 0) {
                $variacion = (($ingresosPeriodo - $ingresosPeriodoAnterior) / $ingresosPeriodoAnterior) * 100;
            }

            // Ingresos por día
            $ingresosPorDia = Reserva::where('estado', 'pagada')
                ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                ->select(
                    DB::raw('DATE(fecha_entrada) as fecha'),
                    DB::raw('SUM(precio_total) as total')
                )
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            // Ingresos por tipo de habitación
            $ingresosPorTipo = Reserva::where('estado', 'pagada')
                ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                ->join('habitacions', 'reservas.habitacion_id', '=', 'habitacions.id')
                ->join('tipo_habitacions', 'habitacions.tipo_habitacion_id', '=', 'tipo_habitacions.id')
                ->select(
                    'tipo_habitacions.nombre',
                    DB::raw('SUM(reservas.precio_total) as total'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->groupBy('tipo_habitacions.nombre')
                ->get();

            return response()->json([
                'success' => true,
                'ingresos' => [
                    'periodo_actual' => $ingresosPeriodo,
                    'periodo_anterior' => $ingresosPeriodoAnterior,
                    'variacion_porcentual' => round($variacion, 2),
                    'por_dia' => $ingresosPorDia,
                    'por_tipo_habitacion' => $ingresosPorTipo
                ],
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte de ingresos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de ingresos'
            ], 500);
        }
    }

    /**
     * Reporte de ocupación y RevPAR
     */
    public function reporteOcupacion(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());

            $totalHabitaciones = Habitacion::count();
            $diasPeriodo = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
            $habitacionesDisponiblesPeriodo = $totalHabitaciones * $diasPeriodo;

            // Calcular noches ocupadas
            $nochesOcupadas = 0;
            $reservas = Reserva::whereIn('estado', ['pagada', 'en_curso', 'completada'])
                ->where(function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_salida', [$fechaInicio, $fechaFin])
                        ->orWhere(function($q) use ($fechaInicio, $fechaFin) {
                            $q->where('fecha_entrada', '<=', $fechaInicio)
                              ->where('fecha_salida', '>=', $fechaFin);
                        });
                })
                ->get();

            foreach ($reservas as $reserva) {
                $entrada = max(Carbon::parse($reserva->fecha_entrada), Carbon::parse($fechaInicio));
                $salida = min(Carbon::parse($reserva->fecha_salida), Carbon::parse($fechaFin));
                $nochesOcupadas += $entrada->diffInDays($salida);
            }

            // Tasa de ocupación
            $tasaOcupacion = $habitacionesDisponiblesPeriodo > 0
                ? ($nochesOcupadas / $habitacionesDisponiblesPeriodo) * 100
                : 0;

            // RevPAR (Revenue Per Available Room)
            $ingresosTotal = Reserva::where('estado', 'pagada')
                ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                ->sum('precio_total');

            $revPAR = $habitacionesDisponiblesPeriodo > 0
                ? $ingresosTotal / ($totalHabitaciones * $diasPeriodo)
                : 0;

            // ADR (Average Daily Rate) - Tarifa promedio por habitación
            $adr = $nochesOcupadas > 0
                ? $ingresosTotal / $nochesOcupadas
                : 0;

            // Ocupación por día
            $ocupacionPorDia = [];
            $currentDate = Carbon::parse($fechaInicio);
            $endDate = Carbon::parse($fechaFin);

            while ($currentDate <= $endDate) {
                $habitacionesOcupadas = Reserva::whereIn('estado', ['pagada', 'en_curso', 'completada'])
                    ->where('fecha_entrada', '<=', $currentDate)
                    ->where('fecha_salida', '>', $currentDate)
                    ->count();

                $ocupacionPorDia[] = [
                    'fecha' => $currentDate->toDateString(),
                    'ocupadas' => $habitacionesOcupadas,
                    'disponibles' => $totalHabitaciones - $habitacionesOcupadas,
                    'tasa' => $totalHabitaciones > 0 ? ($habitacionesOcupadas / $totalHabitaciones) * 100 : 0
                ];

                $currentDate->addDay();
            }

            return response()->json([
                'success' => true,
                'ocupacion' => [
                    'total_habitaciones' => $totalHabitaciones,
                    'noches_ocupadas' => $nochesOcupadas,
                    'noches_disponibles' => $habitacionesDisponiblesPeriodo,
                    'tasa_ocupacion' => round($tasaOcupacion, 2),
                    'revpar' => round($revPAR, 2),
                    'adr' => round($adr, 2),
                    'por_dia' => $ocupacionPorDia
                ],
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin,
                    'dias' => $diasPeriodo
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte de ocupación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de ocupación'
            ], 500);
        }
    }

    /**
     * Reporte de análisis de clientes
     */
    public function reporteClientes(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            $totalClientes = Cliente::count();
            $clientesConReservas = Cliente::has('reservas')->count();

            // Clientes nuevos en el periodo
            $clientesNuevos = 0;
            if ($fechaInicio && $fechaFin) {
                $clientesNuevos = Cliente::whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
            }

            // Top 10 clientes por ingresos
            $topClientes = Cliente::select('clientes.*')
                ->join('reservas', 'clientes.cedula', '=', 'reservas.cliente_cedula')
                ->where('reservas.estado', 'pagada')
                ->groupBy('clientes.cedula', 'clientes.nombre', 'clientes.apellido', 'clientes.email', 'clientes.telefono', 'clientes.created_at', 'clientes.updated_at')
                ->selectRaw('SUM(reservas.precio_total) as total_gastado')
                ->selectRaw('COUNT(reservas.id) as num_reservas')
                ->orderByDesc('total_gastado')
                ->limit(10)
                ->get();

            // Distribución de clientes por número de reservas
            $distribucionReservas = Cliente::withCount('reservas')
                ->get()
                ->groupBy(function($cliente) {
                    if ($cliente->reservas_count == 0) return '0';
                    if ($cliente->reservas_count <= 2) return '1-2';
                    if ($cliente->reservas_count <= 5) return '3-5';
                    if ($cliente->reservas_count <= 10) return '6-10';
                    return '10+';
                })
                ->map(function($group) {
                    return $group->count();
                });

            return response()->json([
                'success' => true,
                'clientes' => [
                    'total' => $totalClientes,
                    'con_reservas' => $clientesConReservas,
                    'nuevos_periodo' => $clientesNuevos,
                    'top_clientes' => $topClientes,
                    'distribucion_reservas' => $distribucionReservas
                ],
                'periodo' => $fechaInicio && $fechaFin ? [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ] : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte de clientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de clientes'
            ], 500);
        }
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'reservas'); // reservas, ingresos, ocupacion, clientes
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());

            $data = [];
            $titulo = '';

            switch ($tipo) {
                case 'reservas':
                    $response = $this->reporteReservas($request);
                    $data = json_decode($response->getContent(), true);
                    $titulo = 'Reporte de Reservas';
                    break;
                case 'ingresos':
                    $response = $this->reporteIngresos($request);
                    $data = json_decode($response->getContent(), true);
                    $titulo = 'Reporte de Ingresos';
                    break;
                case 'ocupacion':
                    $response = $this->reporteOcupacion($request);
                    $data = json_decode($response->getContent(), true);
                    $titulo = 'Reporte de Ocupación';
                    break;
                case 'clientes':
                    $response = $this->reporteClientes($request);
                    $data = json_decode($response->getContent(), true);
                    $titulo = 'Reporte de Clientes';
                    break;
            }

            $pdf = PDF::loadView('reportes.pdf', [
                'titulo' => $titulo,
                'data' => $data,
                'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
                'periodo' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ]);

            $nombreArchivo = strtolower(str_replace(' ', '_', $titulo)) . '_' . Carbon::now()->format('YmdHis') . '.pdf';

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            Log::error('Error al exportar PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'reservas');
            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());

            $data = [];
            $headers = [];
            $nombreArchivo = '';

            switch ($tipo) {
                case 'reservas':
                    $reservas = Reserva::with(['cliente', 'habitacion.tipoHabitacion'])
                        ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                        ->get();

                    $headers = ['ID', 'Cliente', 'Habitación', 'Tipo', 'Entrada', 'Salida', 'Estado', 'Precio'];
                    foreach ($reservas as $reserva) {
                        $data[] = [
                            $reserva->id,
                            $reserva->cliente->nombre . ' ' . $reserva->cliente->apellido,
                            $reserva->habitacion->numero_habitacion ?? 'N/A',
                            $reserva->habitacion->tipoHabitacion->nombre ?? 'N/A',
                            $reserva->fecha_entrada,
                            $reserva->fecha_salida,
                            $reserva->estado,
                            $reserva->precio_total
                        ];
                    }
                    $nombreArchivo = 'reporte_reservas_' . Carbon::now()->format('YmdHis') . '.csv';
                    break;

                case 'clientes':
                    $clientes = Cliente::withCount('reservas')->get();

                    $headers = ['Cédula', 'Nombre', 'Apellido', 'Email', 'Teléfono', 'Num. Reservas', 'Total Gastado'];
                    foreach ($clientes as $cliente) {
                        $totalGastado = Reserva::where('cliente_cedula', $cliente->cedula)
                            ->where('estado', 'pagada')
                            ->sum('precio_total');

                        $data[] = [
                            $cliente->cedula,
                            $cliente->nombre,
                            $cliente->apellido,
                            $cliente->email,
                            $cliente->telefono,
                            $cliente->reservas_count,
                            $totalGastado
                        ];
                    }
                    $nombreArchivo = 'reporte_clientes_' . Carbon::now()->format('YmdHis') . '.csv';
                    break;
            }

            // Generar CSV
            $callback = function() use ($headers, $data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al exportar Excel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar Excel'
            ], 500);
        }
    }
}
