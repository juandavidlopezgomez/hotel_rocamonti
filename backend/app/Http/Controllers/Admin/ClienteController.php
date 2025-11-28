<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->input('busqueda', '');

        $clientes = Cliente::withCount('reservas')
            ->with(['reservas' => function($query) {
                $query->where('estado', 'pagada')
                    ->select('cliente_id', DB::raw('SUM(precio_total) as total_gastado'))
                    ->groupBy('cliente_id');
            }])
            ->when($busqueda, function($query) use ($busqueda) {
                $query->where(function($q) use ($busqueda) {
                    $q->where('nombre', 'like', "%{$busqueda}%")
                      ->orWhere('apellido', 'like', "%{$busqueda}%")
                      ->orWhere('cedula', 'like', "%{$busqueda}%")
                      ->orWhere('email', 'like', "%{$busqueda}%")
                      ->orWhere('telefono', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($cliente) {
                $totalGastado = Reserva::where('cliente_id', $cliente->id)
                    ->where('estado', 'pagada')
                    ->sum('precio_total');

                $ultimaReserva = Reserva::where('cliente_id', $cliente->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                return [
                    'id' => $cliente->id,
                    'nombre' => $cliente->nombre,
                    'apellido' => $cliente->apellido,
                    'cedula' => $cliente->cedula,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'num_reservas' => $cliente->reservas_count,
                    'total_gastado' => $totalGastado,
                    'ultima_reserva' => $ultimaReserva ? $ultimaReserva->fecha_entrada : null
                ];
            });

        return response()->json([
            'success' => true,
            'clientes' => $clientes
        ]);
    }

    public function stats()
    {
        $total = Cliente::count();
        $conReservas = Cliente::has('reservas')->count();

        $ingresosTotales = Reserva::where('estado', 'pagada')->sum('precio_total');

        $clienteVIP = Cliente::withCount('reservas')
            ->orderBy('reservas_count', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'con_reservas' => $conReservas,
                'ingresos_totales' => $ingresosTotales,
                'cliente_vip' => $clienteVIP ? [
                    'nombre' => $clienteVIP->nombre . ' ' . $clienteVIP->apellido,
                    'reservas' => $clienteVIP->reservas_count
                ] : null
            ]
        ]);
    }

    public function show($id)
    {
        $cliente = Cliente::with('reservas')->findOrFail($id);

        return response()->json([
            'success' => true,
            'cliente' => $cliente
        ]);
    }

    public function historial($id)
    {
        $reservas = Reserva::where('cliente_id', $id)
            ->with('tipoHabitacion')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($reserva) {
                return [
                    'id' => $reserva->id,
                    'fecha_reserva' => $reserva->created_at->format('d/m/Y'),
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'habitacion' => $reserva->tipoHabitacion->nombre,
                    'estado' => $reserva->estado,
                    'precio_total' => $reserva->precio_total
                ];
            });

        return response()->json([
            'success' => true,
            'reservas' => $reservas
        ]);
    }
}
