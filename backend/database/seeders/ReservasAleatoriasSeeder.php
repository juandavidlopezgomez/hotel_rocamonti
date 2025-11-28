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

        // Generar 20 reservas aleatorias
        $numReservas = 20;
        $this->command->info("Generando {$numReservas} reservas aleatorias...");

        for ($i = 0; $i < $numReservas; $i++) {
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

            // Generar fechas aleatorias (entre hoy y los próximos 90 días)
            $diasAdelante = rand(0, 90);
            $fechaEntrada = Carbon::now()->addDays($diasAdelante);
            $numNoches = rand(2, 7); // Entre 2 y 7 noches
            $fechaSalida = $fechaEntrada->copy()->addDays($numNoches);

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

            // Si hay conflicto, intentar con otra habitación o fecha
            $intentos = 0;
            while ($conflicto && $intentos < 5) {
                $habitacion = $habitaciones->random();
                $diasAdelante = rand(0, 90);
                $fechaEntrada = Carbon::now()->addDays($diasAdelante);
                $fechaSalida = $fechaEntrada->copy()->addDays($numNoches);

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

            // Si después de 5 intentos sigue habiendo conflicto, saltar esta reserva
            if ($conflicto) {
                $numReserva = $i + 1;
                $this->command->warn("No se pudo crear la reserva #{$numReserva} debido a conflictos de fechas.");
                continue;
            }

            // Calcular datos de la reserva
            $numAdultos = rand(1, $habitacion->tipoHabitacion->capacidad_adultos);
            $numNinos = rand(0, $habitacion->tipoHabitacion->capacidad_ninos);
            $precioTotal = $habitacion->tipoHabitacion->precio_base * $numNoches;

            // Estado aleatorio (mayoría pagadas)
            $estados = ['pagada', 'pagada', 'pagada', 'pendiente'];
            $estado = $estados[array_rand($estados)];

            // Crear la reserva
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

            $numReserva = $i + 1;
            $this->command->info("Reserva #{$numReserva} creada: {$cliente->nombre} {$cliente->apellido} - Hab. {$habitacion->numero_habitacion} ({$fechaEntrada->format('Y-m-d')} a {$fechaSalida->format('Y-m-d')}) - Estado: {$estado}");
        }

        $this->command->info("\n✓ Reservas aleatorias creadas exitosamente!");
    }
}
