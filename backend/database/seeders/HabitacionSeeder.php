<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitacionNum = 101;

        // Crear SOLO 1 habitación por cada tipo (total 9 habitaciones)
        // 3 apartamentos (tipos 1, 2, 5) + 6 habitaciones (tipos 3, 4, 6, 7, 8, 9)
        for ($tipoId = 1; $tipoId <= 9; $tipoId++) {
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

        echo "✅ Se crearon 9 habitaciones (3 apartamentos + 6 habitaciones)\n";
    }
}
