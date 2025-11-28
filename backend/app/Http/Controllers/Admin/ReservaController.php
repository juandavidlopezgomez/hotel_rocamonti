<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Log::info('Admin ReservaController@index reached.');
        try {
            $reservas = Reserva::with(['cliente', 'habitacion.tipoHabitacion'])->latest()->paginate(20);
            \Illuminate\Support\Facades\Log::info('Reservas query successful.', ['count' => $reservas->count()]);
            return response()->json(['success' => true, 'data' => $reservas]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in Admin ReservaController@index: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['cliente', 'habitacion.tipoHabitacion', 'pago']);
        return response()->json(['success' => true, 'data' => $reserva]);
    }
}
