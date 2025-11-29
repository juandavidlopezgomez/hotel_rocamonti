<?php

namespace App\Services;

use App\Models\TipoHabitacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PricingService
{
    const TEMPORADA_ALTA = 'alta';
    const TEMPORADA_MEDIA = 'media';
    const TEMPORADA_BAJA = 'baja';

    /**
     * Calcular precio dinámico de una habitación
     */
    public function calcularPrecioDinamico($tipoHabitacionId, $fechaEntrada, $fechaSalida)
    {
        $tipoHabitacion = TipoHabitacion::findOrFail($tipoHabitacionId);
        $precioBase = $tipoHabitacion->precio_base;

        $fechaInicio = Carbon::parse($fechaEntrada);
        $fechaFin = Carbon::parse($fechaSalida);
        $noches = $fechaInicio->diffInDays($fechaFin);

        if ($noches <= 0) {
            throw new \Exception('La fecha de salida debe ser posterior a la fecha de entrada');
        }

        $precioTotal = 0;
        $fechaActual = $fechaInicio->copy();

        // Calcular precio día por día
        while ($fechaActual->lessThan($fechaFin)) {
            $precioDia = $this->calcularPrecioDia($precioBase, $fechaActual);
            $precioTotal += $precioDia;
            $fechaActual->addDay();
        }

        // Aplicar descuentos por estancia prolongada
        $descuento = $this->calcularDescuentoPorEstancia($noches);
        $precioTotal = $precioTotal * (1 - $descuento);

        return round($precioTotal, 2);
    }

    /**
     * Calcular precio para un día específico
     */
    protected function calcularPrecioDia($precioBase, Carbon $fecha)
    {
        $precio = $precioBase;

        // Aplicar multiplicador de temporada
        $temporada = $this->determinarTemporada($fecha);
        $precio *= $this->getMultiplicadorTemporada($temporada);

        // Recargo para fines de semana
        if ($this->esFinDeSemana($fecha)) {
            $precio *= 1.15; // 15% recargo
        }

        // Verificar si es fecha especial
        if ($fechaEspecial = $this->esFechaEspecial($fecha)) {
            $precio *= $fechaEspecial['multiplicador'];
        }

        return $precio;
    }

    /**
     * Determinar temporada según la fecha
     */
    protected function determinarTemporada(Carbon $fecha)
    {
        $mes = $fecha->month;

        // Temporada Alta: Diciembre, Enero, Junio, Julio
        if (in_array($mes, [12, 1, 6, 7])) {
            return self::TEMPORADA_ALTA;
        }

        // Temporada Baja: Febrero, Marzo, Abril, Mayo, Agosto, Septiembre
        if (in_array($mes, [2, 3, 4, 5, 8, 9])) {
            return self::TEMPORADA_BAJA;
        }

        // Temporada Media: Octubre, Noviembre
        return self::TEMPORADA_MEDIA;
    }

    /**
     * Obtener multiplicador de temporada
     */
    protected function getMultiplicadorTemporada($temporada)
    {
        $multiplicadores = [
            self::TEMPORADA_ALTA => 1.30,  // 30% más
            self::TEMPORADA_MEDIA => 1.10, // 10% más
            self::TEMPORADA_BAJA => 0.90,  // 10% menos
        ];

        return $multiplicadores[$temporada] ?? 1.0;
    }

    /**
     * Verificar si es fin de semana
     */
    protected function esFinDeSemana(Carbon $fecha)
    {
        return $fecha->isFriday() || $fecha->isSaturday();
    }

    /**
     * Verificar si es fecha especial
     */
    protected function esFechaEspecial(Carbon $fecha)
    {
        $fechasEspeciales = $this->obtenerFechasEspeciales();

        $fechaStr = $fecha->format('m-d');

        foreach ($fechasEspeciales as $especial) {
            if ($especial['fecha'] === $fechaStr) {
                return $especial;
            }
        }

        return null;
    }

    /**
     * Obtener fechas especiales configuradas
     */
    protected function obtenerFechasEspeciales()
    {
        // TODO: Mover esto a configuración en base de datos
        return [
            ['fecha' => '12-25', 'nombre' => 'Navidad', 'multiplicador' => 1.50],
            ['fecha' => '12-31', 'nombre' => 'Año Nuevo', 'multiplicador' => 1.50],
            ['fecha' => '01-01', 'nombre' => 'Año Nuevo', 'multiplicador' => 1.50],
            ['fecha' => '06-24', 'nombre' => 'San Juan', 'multiplicador' => 1.40],
            ['fecha' => '12-08', 'nombre' => 'Día de las Velitas', 'multiplicador' => 1.30],
        ];
    }

    /**
     * Calcular descuento por estancia prolongada
     */
    protected function calcularDescuentoPorEstancia($noches)
    {
        if ($noches >= 30) {
            return 0.20; // 20% descuento para estancias de 30+ noches
        } elseif ($noches >= 14) {
            return 0.15; // 15% descuento para estancias de 14+ noches
        } elseif ($noches >= 7) {
            return 0.10; // 10% descuento para estancias de 7+ noches
        } elseif ($noches >= 3) {
            return 0.05; // 5% descuento para estancias de 3+ noches
        }

        return 0; // Sin descuento
    }

    /**
     * Obtener calendario de precios
     */
    public function obtenerCalendarioPrecios($tipoHabitacionId, $fechaInicio, $fechaFin)
    {
        $tipoHabitacion = TipoHabitacion::findOrFail($tipoHabitacionId);
        $precioBase = $tipoHabitacion->precio_base;

        $fechaActual = Carbon::parse($fechaInicio);
        $fechaFinal = Carbon::parse($fechaFin);

        $calendario = [];

        while ($fechaActual->lessThanOrEqualTo($fechaFinal)) {
            $precioDia = $this->calcularPrecioDia($precioBase, $fechaActual);
            $temporada = $this->determinarTemporada($fechaActual);

            $calendario[] = [
                'fecha' => $fechaActual->format('Y-m-d'),
                'dia_semana' => $fechaActual->locale('es')->dayName,
                'precio' => round($precioDia, 2),
                'precio_base' => $precioBase,
                'temporada' => $temporada,
                'es_fin_semana' => $this->esFinDeSemana($fechaActual),
                'es_especial' => $this->esFechaEspecial($fechaActual) !== null,
            ];

            $fechaActual->addDay();
        }

        return $calendario;
    }

    /**
     * Actualizar precio base de un tipo de habitación
     */
    public function actualizarPrecioBase($tipoHabitacionId, $nuevoPrecio)
    {
        $tipoHabitacion = TipoHabitacion::findOrFail($tipoHabitacionId);

        $tipoHabitacion->update([
            'precio_base' => $nuevoPrecio
        ]);

        // Limpiar cache de precios si existe
        Cache::tags(['pricing'])->flush();

        return $tipoHabitacion;
    }

    /**
     * Obtener sugerencia de precio óptimo
     */
    public function sugerirPrecioOptimo($tipoHabitacionId, $fechaEntrada, $fechaSalida, $tasaOcupacionActual)
    {
        $precioBase = $this->calcularPrecioDinamico($tipoHabitacionId, $fechaEntrada, $fechaSalida);

        // Ajustar según tasa de ocupación
        if ($tasaOcupacionActual > 80) {
            // Alta ocupación: aumentar precios
            $precioBase *= 1.15;
        } elseif ($tasaOcupacionActual < 40) {
            // Baja ocupación: reducir precios para atraer clientes
            $precioBase *= 0.90;
        }

        return round($precioBase, 2);
    }

    /**
     * Calcular precio promedio para un período
     */
    public function calcularPrecioPromedio($tipoHabitacionId, $fechaInicio, $fechaFin)
    {
        $calendario = $this->obtenerCalendarioPrecios($tipoHabitacionId, $fechaInicio, $fechaFin);

        $totalPrecios = array_sum(array_column($calendario, 'precio'));
        $diasTotales = count($calendario);

        return $diasTotales > 0 ? round($totalPrecios / $diasTotales, 2) : 0;
    }

    /**
     * Obtener análisis de competitividad de precios
     */
    public function analisisCompetitividad($tipoHabitacionId)
    {
        $tipoHabitacion = TipoHabitacion::findOrFail($tipoHabitacionId);

        // Obtener precios de tipos similares
        $preciosSimilares = TipoHabitacion::where('id', '!=', $tipoHabitacionId)
            ->where('capacidad_adultos', $tipoHabitacion->capacidad_adultos)
            ->pluck('precio_base')
            ->toArray();

        if (empty($preciosSimilares)) {
            return [
                'precio_actual' => $tipoHabitacion->precio_base,
                'posicion' => 'unico',
                'recomendacion' => 'No hay habitaciones comparables',
            ];
        }

        $precioPromedio = array_sum($preciosSimilares) / count($preciosSimilares);
        $precioMinimo = min($preciosSimilares);
        $precioMaximo = max($preciosSimilares);

        $posicion = 'competitivo';
        if ($tipoHabitacion->precio_base > $precioPromedio * 1.15) {
            $posicion = 'alto';
        } elseif ($tipoHabitacion->precio_base < $precioPromedio * 0.85) {
            $posicion = 'bajo';
        }

        return [
            'precio_actual' => $tipoHabitacion->precio_base,
            'precio_promedio_mercado' => round($precioPromedio, 2),
            'precio_minimo' => $precioMinimo,
            'precio_maximo' => $precioMaximo,
            'posicion' => $posicion,
            'diferencia_porcentual' => round((($tipoHabitacion->precio_base - $precioPromedio) / $precioPromedio) * 100, 2),
        ];
    }
}
