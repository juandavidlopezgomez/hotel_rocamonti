<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitacionNum = 101;

        // Crear 2-3 habitaciones de cada tipo
        for ($tipoId = 1; $tipoId <= 9; $tipoId++) {
            $numHabitaciones = ($tipoId % 3 === 0) ? 2 : 3; // Alternar entre 2 y 3

            for ($i = 0; $i < $numHabitaciones; $i++) {
                DB::table('habitacions')->insert([
                    'numero_habitacion' => (string)$habitacionNum,
                    'tipo_habitacion_id' => $tipoId,
                    'piso' => (int)(($habitacionNum - 1) / 10) + 1,
                    'estado' => 'disponible',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $habitacionNum++;
            }
        }
    }
}
