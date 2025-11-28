<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HabitacionController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::with(['tipoHabitacion'])
            ->get()
            ->map(function($habitacion) {
                $reservaActual = Reserva::where('habitacion_id', $habitacion->id)
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->whereIn('estado', ['pagada', 'pendiente'])
                    ->with('cliente')
                    ->first();

                return [
                    'id' => $habitacion->id,
                    'numero' => $habitacion->numero,
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
    }

    public function stats()
    {
        $total = Habitacion::count();
        $disponibles = Habitacion::where('estado', 'disponible')->count();
        $ocupadas = Habitacion::where('estado', 'ocupada')->count();
        $mantenimiento = Habitacion::where('estado', 'mantenimiento')->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'disponibles' => $disponibles,
                'ocupadas' => $ocupadas,
                'mantenimiento' => $mantenimiento
            ]
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:disponible,ocupada,mantenimiento'
        ]);

        $habitacion = Habitacion::findOrFail($id);

        // Verificar si hay reserva activa
        $reservaActiva = Reserva::where('habitacion_id', $id)
            ->where('fecha_entrada', '<=', now())
            ->where('fecha_salida', '>=', now())
            ->whereIn('estado', ['pagada', 'pendiente'])
            ->exists();

        if ($request->estado !== 'ocupada' && $reservaActiva) {
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
            'habitacion' => $habitacion
        ]);
    }
}
