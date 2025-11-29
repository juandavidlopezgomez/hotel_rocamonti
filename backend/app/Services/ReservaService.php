<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Habitacion;
use App\Models\Cliente;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservaService
{
    /**
     * Verificar disponibilidad de habitación
     */
    public function verificarDisponibilidad($habitacionId, $fechaEntrada, $fechaSalida, $reservaIdExcluir = null)
    {
        $query = Reserva::where('habitacion_id', $habitacionId)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($q) use ($fechaEntrada, $fechaSalida) {
                $q->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                  ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                  ->orWhere(function ($q2) use ($fechaEntrada, $fechaSalida) {
                      $q2->where('fecha_entrada', '<=', $fechaEntrada)
                         ->where('fecha_salida', '>=', $fechaSalida);
                  });
            });

        if ($reservaIdExcluir) {
            $query->where('id', '!=', $reservaIdExcluir);
        }

        $reservasConflicto = $query->count();

        return $reservasConflicto === 0;
    }

    /**
     * Buscar habitaciones disponibles
     */
    public function buscarHabitacionesDisponibles($fechaEntrada, $fechaSalida, $tipoHabitacionId = null)
    {
        $habitacionesQuery = Habitacion::with('tipoHabitacion')
            ->where('estado', 'disponible');

        if ($tipoHabitacionId) {
            $habitacionesQuery->where('tipo_habitacion_id', $tipoHabitacionId);
        }

        $habitaciones = $habitacionesQuery->get();

        return $habitaciones->filter(function ($habitacion) use ($fechaEntrada, $fechaSalida) {
            return $this->verificarDisponibilidad($habitacion->id, $fechaEntrada, $fechaSalida);
        });
    }

    /**
     * Crear una nueva reserva
     */
    public function crearReserva(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            // Verificar disponibilidad
            if (!$this->verificarDisponibilidad(
                $datos['habitacion_id'],
                $datos['fecha_entrada'],
                $datos['fecha_salida']
            )) {
                throw new \Exception('La habitación no está disponible para las fechas seleccionadas');
            }

            // Crear o actualizar cliente
            $cliente = Cliente::updateOrCreate(
                ['cedula' => $datos['cliente_cedula']],
                [
                    'nombre' => $datos['cliente_nombre'] ?? null,
                    'apellido' => $datos['cliente_apellido'] ?? null,
                    'email' => $datos['cliente_email'] ?? null,
                    'telefono' => $datos['cliente_telefono'] ?? null,
                ]
            );

            // Calcular precio total
            $precioTotal = $this->calcularPrecioReserva(
                $datos['habitacion_id'],
                $datos['fecha_entrada'],
                $datos['fecha_salida']
            );

            // Crear reserva
            $reserva = Reserva::create([
                'cliente_cedula' => $cliente->cedula,
                'habitacion_id' => $datos['habitacion_id'],
                'fecha_entrada' => $datos['fecha_entrada'],
                'fecha_salida' => $datos['fecha_salida'],
                'num_adultos' => $datos['num_adultos'],
                'num_ninos' => $datos['num_ninos'] ?? 0,
                'precio_total' => $precioTotal,
                'estado' => $datos['estado'] ?? 'pendiente',
            ]);

            // Actualizar estado de la habitación si es necesario
            $this->actualizarEstadoHabitacion($reserva);

            return $reserva->load(['cliente', 'habitacion.tipoHabitacion']);
        });
    }

    /**
     * Actualizar una reserva
     */
    public function actualizarReserva($id, array $datos)
    {
        return DB::transaction(function () use ($id, $datos) {
            $reserva = Reserva::findOrFail($id);

            // Si cambian las fechas o la habitación, verificar disponibilidad
            if (
                isset($datos['fecha_entrada']) && $datos['fecha_entrada'] != $reserva->fecha_entrada ||
                isset($datos['fecha_salida']) && $datos['fecha_salida'] != $reserva->fecha_salida ||
                isset($datos['habitacion_id']) && $datos['habitacion_id'] != $reserva->habitacion_id
            ) {
                $habitacionId = $datos['habitacion_id'] ?? $reserva->habitacion_id;
                $fechaEntrada = $datos['fecha_entrada'] ?? $reserva->fecha_entrada;
                $fechaSalida = $datos['fecha_salida'] ?? $reserva->fecha_salida;

                if (!$this->verificarDisponibilidad($habitacionId, $fechaEntrada, $fechaSalida, $id)) {
                    throw new \Exception('La habitación no está disponible para las nuevas fechas');
                }

                // Recalcular precio si cambian fechas o habitación
                $datos['precio_total'] = $this->calcularPrecioReserva(
                    $habitacionId,
                    $fechaEntrada,
                    $fechaSalida
                );
            }

            $reserva->update($datos);

            // Actualizar estado de las habitaciones involucradas
            $this->actualizarEstadoHabitacion($reserva);

            return $reserva->load(['cliente', 'habitacion.tipoHabitacion']);
        });
    }

    /**
     * Cancelar una reserva
     */
    public function cancelarReserva($id, $motivo = null)
    {
        return DB::transaction(function () use ($id, $motivo) {
            $reserva = Reserva::findOrFail($id);

            if ($reserva->estado === 'cancelada') {
                throw new \Exception('La reserva ya está cancelada');
            }

            $reserva->update([
                'estado' => 'cancelada',
                'motivo_cancelacion' => $motivo,
                'cancelada_at' => now(),
            ]);

            // Liberar la habitación
            $habitacion = $reserva->habitacion;
            if ($habitacion && $habitacion->estado === 'ocupada') {
                $habitacion->update(['estado' => 'disponible']);
            }

            return $reserva;
        });
    }

    /**
     * Realizar check-in
     */
    public function checkIn($id)
    {
        return DB::transaction(function () use ($id) {
            $reserva = Reserva::findOrFail($id);

            if ($reserva->estado === 'cancelada') {
                throw new \Exception('No se puede hacer check-in de una reserva cancelada');
            }

            if ($reserva->estado === 'en_curso') {
                throw new \Exception('Ya se realizó el check-in para esta reserva');
            }

            $reserva->update([
                'estado' => 'en_curso',
                'checkin_at' => now(),
            ]);

            // Actualizar estado de la habitación
            $reserva->habitacion->update(['estado' => 'ocupada']);

            return $reserva;
        });
    }

    /**
     * Realizar check-out
     */
    public function checkOut($id, $consumosAdicionales = 0)
    {
        return DB::transaction(function () use ($id, $consumosAdicionales) {
            $reserva = Reserva::findOrFail($id);

            if ($reserva->estado !== 'en_curso') {
                throw new \Exception('Solo se puede hacer check-out de reservas en curso');
            }

            $totalFinal = $reserva->precio_total + $consumosAdicionales;

            $reserva->update([
                'estado' => 'completada',
                'checkout_at' => now(),
                'consumos_adicionales' => $consumosAdicionales,
                'total_final' => $totalFinal,
            ]);

            // Actualizar estado de la habitación a limpieza
            $reserva->habitacion->update(['estado' => 'limpieza']);

            return $reserva;
        });
    }

    /**
     * Calcular precio de la reserva
     */
    protected function calcularPrecioReserva($habitacionId, $fechaEntrada, $fechaSalida)
    {
        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($habitacionId);
        $precioBase = $habitacion->tipoHabitacion->precio_base;

        $fechaInicio = Carbon::parse($fechaEntrada);
        $fechaFin = Carbon::parse($fechaSalida);
        $noches = $fechaInicio->diffInDays($fechaFin);

        if ($noches <= 0) {
            throw new \Exception('La fecha de salida debe ser posterior a la fecha de entrada');
        }

        // Por ahora, precio simple: precio_base * número de noches
        // TODO: Implementar lógica de pricing dinámico (temporadas, descuentos, etc.)
        $precioTotal = $precioBase * $noches;

        return $precioTotal;
    }

    /**
     * Actualizar estado de la habitación según la reserva
     */
    protected function actualizarEstadoHabitacion(Reserva $reserva)
    {
        $habitacion = $reserva->habitacion;

        if (!$habitacion) {
            return;
        }

        $hoy = Carbon::today();
        $fechaEntrada = Carbon::parse($reserva->fecha_entrada);

        // Si la reserva está en curso, la habitación está ocupada
        if ($reserva->estado === 'en_curso') {
            $habitacion->update(['estado' => 'ocupada']);
        }
        // Si la reserva es para hoy y está pagada, preparar la habitación
        elseif ($reserva->estado === 'pagada' && $fechaEntrada->isSameDay($hoy)) {
            if ($habitacion->estado === 'disponible') {
                $habitacion->update(['estado' => 'limpieza']);
            }
        }
    }

    /**
     * Obtener reservas del día
     */
    public function reservasDelDia()
    {
        $hoy = Carbon::today();

        return [
            'llegadas' => Reserva::with(['cliente', 'habitacion.tipoHabitacion'])
                ->whereDate('fecha_entrada', $hoy)
                ->where('estado', '!=', 'cancelada')
                ->get(),

            'salidas' => Reserva::with(['cliente', 'habitacion.tipoHabitacion'])
                ->whereDate('fecha_salida', $hoy)
                ->where('estado', '!=', 'cancelada')
                ->where('estado', '!=', 'completada')
                ->get(),
        ];
    }

    /**
     * Obtener estadísticas de reservas
     */
    public function obtenerEstadisticas($fechaInicio = null, $fechaFin = null)
    {
        $query = Reserva::query();

        if ($fechaInicio) {
            $query->where('fecha_entrada', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->where('fecha_entrada', '<=', $fechaFin);
        }

        $reservas = $query->get();

        return [
            'total' => $reservas->count(),
            'pendientes' => $reservas->where('estado', 'pendiente')->count(),
            'pagadas' => $reservas->where('estado', 'pagada')->count(),
            'en_curso' => $reservas->where('estado', 'en_curso')->count(),
            'completadas' => $reservas->where('estado', 'completada')->count(),
            'canceladas' => $reservas->where('estado', 'cancelada')->count(),
            'ingresos_totales' => $reservas->whereIn('estado', ['pagada', 'completada'])->sum('precio_total'),
            'ingresos_pendientes' => $reservas->where('estado', 'pendiente')->sum('precio_total'),
        ];
    }
}
