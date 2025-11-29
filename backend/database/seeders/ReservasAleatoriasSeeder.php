<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Habitacion;
use Carbon\Carbon;

class ReservasAleatoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Arrays de nombres y apellidos aleatorios
        $nombres = [
            'Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Laura', 'Pedro', 'Carmen',
            'José', 'Isabel', 'Miguel', 'Patricia', 'David', 'Sofía', 'Jorge',
            'Valentina', 'Diego', 'Camila', 'Andrés', 'Daniela', 'Felipe', 'Natalia',
            'Sebastián', 'Gabriela', 'Ricardo', 'Lucía', 'Fernando', 'Martina',
            'Rafael', 'Victoria', 'Alejandro', 'Catalina', 'Roberto', 'Paula',
            'Manuel', 'Andrea', 'Antonio', 'Carolina', 'Francisco', 'Juliana'
        ];

        $apellidos = [
            'García', 'Rodríguez', 'Martínez', 'López', 'González', 'Pérez',
            'Sánchez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Gómez',
            'Díaz', 'Cruz', 'Morales', 'Reyes', 'Ortiz', 'Gutiérrez',
            'Chávez', 'Ruiz', 'Hernández', 'Mendoza', 'Vargas', 'Castro',
            'Romero', 'Suárez', 'Moreno', 'Jiménez', 'Alvarez', 'Rojas'
        ];

        // Obtener todas las habitaciones disponibles
        $habitaciones = Habitacion::all();

        if ($habitaciones->isEmpty()) {
            $this->command->error('No hay habitaciones en la base de datos. Ejecuta primero el HabitacionSeeder.');
            return;
        }

        $this->command->info("Generando reservas para probar todos los filtros...");

        // RESERVAS PARA HOY (Llegadas hoy) - 3 reservas
        $this->command->info("\n=== RESERVAS PARA HOY ===");
        for ($i = 0; $i < 3; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today(), // Entrada hoy
                rand(2, 5),
                'pagada' // PAGADA - Pendiente de check-in
            );
        }

        // SALIDAS HOY - 2 reservas
        $this->command->info("\n=== SALIDAS HOY ===");
        for ($i = 0; $i < 2; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->subDays(rand(2, 4)), // Entrada hace días
                rand(2, 4), // Salida hoy
                'pagada', // PAGADA - están en el hotel
                Carbon::today() // Forzar salida hoy
            );
        }

        // RESERVAS PARA MAÑANA - 4 reservas
        $this->command->info("\n=== RESERVAS PARA MAÑANA ===");
        for ($i = 0; $i < 4; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::tomorrow(), // Entrada mañana
                rand(2, 6),
                'pagada' // PAGADA
            );
        }

        // PRÓXIMOS 7 DÍAS (días 2-7 desde hoy) - 6 reservas
        $this->command->info("\n=== PRÓXIMOS 7 DÍAS ===");
        for ($i = 2; $i <= 7; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->addDays($i), // Entrada en días 2-7
                rand(2, 5),
                'pagada' // PAGADA
            );
        }

        // RESERVAS ACTIVAS (huéspedes actualmente en el hotel) - 5 reservas
        $this->command->info("\n=== HUÉSPEDES ACTIVOS (EN EL HOTEL) ===");
        for ($i = 0; $i < 5; $i++) {
            $diasAtras = rand(1, 5);
            $diasEstancia = rand(3, 7);
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->subDays($diasAtras), // Entrada hace días
                $diasEstancia,
                'pagada' // PAGADA - Activa
            );
        }

        // RESERVAS ESTE MES (resto del mes) - 8 reservas
        $this->command->info("\n=== RESERVAS ESTE MES ===");
        $finMes = Carbon::now()->endOfMonth();
        $diasHastaFinMes = Carbon::today()->diffInDays($finMes);

        for ($i = 0; $i < 8; $i++) {
            $diaAleatorio = rand(8, min($diasHastaFinMes, 30));
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->addDays($diaAleatorio),
                rand(2, 5),
                'pagada' // PAGADA
            );
        }

        // RESERVAS COMPLETADAS (check-out ya realizado) - 5 reservas
        $this->command->info("\n=== RESERVAS COMPLETADAS ===");
        for ($i = 0; $i < 5; $i++) {
            $diasAtras = rand(5, 30);
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->subDays($diasAtras), // Entrada hace semanas
                rand(2, 5),
                'pagada', // PAGADA - Completada
                Carbon::today()->subDays(rand(1, 4)) // Salida hace días
            );
        }

        // RESERVAS CANCELADAS - 3 reservas
        $this->command->info("\n=== RESERVAS CANCELADAS ===");
        for ($i = 0; $i < 3; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->addDays(rand(10, 30)),
                rand(2, 5),
                'cancelada'
            );
        }

        // RESERVAS FUTURAS (próximos 2-3 meses) - 10 reservas
        $this->command->info("\n=== RESERVAS FUTURAS ===");
        for ($i = 0; $i < 10; $i++) {
            $this->crearReserva(
                $nombres,
                $apellidos,
                $habitaciones,
                Carbon::today()->addDays(rand(31, 90)),
                rand(2, 7),
                'pagada' // PAGADA
            );
        }

        $totalReservas = Reserva::count();
        $this->command->info("\n✓ Total de reservas creadas: {$totalReservas}");
        $this->command->info("✓ TODAS LAS RESERVAS ESTÁN 100% PAGADAS (obligatorio)");
    }

    private function crearReserva($nombres, $apellidos, $habitaciones, $fechaEntrada, $numNoches, $estado, $fechaSalidaCustom = null)
    {
        // Generar datos aleatorios del cliente
        $nombre = $nombres[array_rand($nombres)];
        $apellido1 = $apellidos[array_rand($apellidos)];
        $apellido2 = $apellidos[array_rand($apellidos)];
        $cedula = rand(10000000, 99999999);
        $email = strtolower($nombre . '.' . $apellido1 . rand(1, 999) . '@gmail.com');
        $telefono = '3' . rand(100000000, 199999999);

        // Crear o actualizar cliente
        $cliente = Cliente::updateOrCreate(
            ['cedula' => (string)$cedula],
            [
                'nombre' => $nombre,
                'apellido' => $apellido1 . ' ' . $apellido2,
                'email' => $email,
                'telefono' => (string)$telefono,
            ]
        );

        // Seleccionar habitación aleatoria
        $habitacion = $habitaciones->random();

        // Calcular fecha de salida
        $fechaSalida = $fechaSalidaCustom ?? $fechaEntrada->copy()->addDays($numNoches);

        // Verificar que no haya conflictos con otras reservas
        $conflicto = Reserva::where('habitacion_id', $habitacion->id)
            ->where('estado', '!=', 'cancelada')
            ->where(function($query) use ($fechaEntrada, $fechaSalida) {
                $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                      ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                      ->orWhere(function($q) use ($fechaEntrada, $fechaSalida) {
                          $q->where('fecha_entrada', '<=', $fechaEntrada)
                            ->where('fecha_salida', '>=', $fechaSalida);
                      });
            })
            ->exists();

        // Si hay conflicto, intentar con otra habitación
        $intentos = 0;
        while ($conflicto && $intentos < 10) {
            $habitacion = $habitaciones->random();

            $conflicto = Reserva::where('habitacion_id', $habitacion->id)
                ->where('estado', '!=', 'cancelada')
                ->where(function($query) use ($fechaEntrada, $fechaSalida) {
                    $query->whereBetween('fecha_entrada', [$fechaEntrada, $fechaSalida])
                          ->orWhereBetween('fecha_salida', [$fechaEntrada, $fechaSalida])
                          ->orWhere(function($q) use ($fechaEntrada, $fechaSalida) {
                              $q->where('fecha_entrada', '<=', $fechaEntrada)
                                ->where('fecha_salida', '>=', $fechaSalida);
                          });
                })
                ->exists();

            $intentos++;
        }

        // Si después de 10 intentos sigue habiendo conflicto, saltar esta reserva
        if ($conflicto) {
            $this->command->warn("No se pudo crear reserva debido a conflictos de fechas.");
            return;
        }

        // Calcular datos de la reserva
        $numAdultos = rand(1, $habitacion->tipoHabitacion->capacidad_adultos);
        $numNinos = rand(0, $habitacion->tipoHabitacion->capacidad_ninos);
        $diasReales = $fechaEntrada->diffInDays($fechaSalida);
        $precioTotal = $habitacion->tipoHabitacion->precio_base * max($diasReales, 1);

        // Crear la reserva - TODAS PAGADAS
        Reserva::create([
            'cliente_cedula' => $cliente->cedula,
            'habitacion_id' => $habitacion->id,
            'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
            'fecha_salida' => $fechaSalida->format('Y-m-d'),
            'num_adultos' => $numAdultos,
            'num_ninos' => $numNinos,
            'precio_total' => $precioTotal,
            'estado' => $estado,
        ]);

        $this->command->info("✓ {$cliente->nombre} {$cliente->apellido} - Hab. {$habitacion->numero_habitacion} ({$fechaEntrada->format('Y-m-d')} → {$fechaSalida->format('Y-m-d')}) - {$estado} - PAGADO");
    }
}
