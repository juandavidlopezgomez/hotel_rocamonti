<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoHabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Apartamento de 1 dormitorio',
                'descripcion_camas' => '2 camas individuales y 1 cama doble',
                'capacidad_adultos' => 4,
                'capacidad_ninos' => 2,
                'precio_base' => 396000,
                'es_apartamento' => true,
                'metros_cuadrados' => 40,
                'amenidades' => json_encode(['Apartamento entero', 'Vistas a la montaña', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Apartamento',
                'descripcion_camas' => 'Dormitorio 1: 1 cama doble, Dormitorio 2: 2 camas individuales, Dormitorio 3: 1 cama individual, Sala de estar: 1 sofá cama',
                'capacidad_adultos' => 5,
                'capacidad_ninos' => 3,
                'precio_base' => 534600,
                'es_apartamento' => true,
                'metros_cuadrados' => 50,
                'amenidades' => json_encode(['Apartamento entero', 'Vistas a la montaña', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Doble Deluxe con vistas al lago - 1 o 2 camas',
                'descripcion_camas' => '1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'precio_base' => 450000,
                'es_apartamento' => false,
                'metros_cuadrados' => 15,
                'amenidades' => json_encode(['Habitación', 'Balcón', 'Vistas al lago', 'Bañera de hidromasaje', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'Sauna', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Familiar Deluxe',
                'descripcion_camas' => '1 cama individual y 1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'precio_base' => 360000,
                'es_apartamento' => false,
                'metros_cuadrados' => 20,
                'amenidades' => json_encode(['Habitación', 'Vistas a un patio interior', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Apartamento',
                'descripcion_camas' => '1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'precio_base' => 451440,
                'es_apartamento' => true,
                'metros_cuadrados' => 50,
                'amenidades' => json_encode(['Apartamento entero', 'Vistas a la montaña', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Familiar con vistas al lago',
                'descripcion_camas' => '1 cama individual y 1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 3,
                'precio_base' => 309840,
                'es_apartamento' => false,
                'metros_cuadrados' => 20,
                'amenidades' => json_encode(['Habitación', 'Vistas al lago', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Doble con vistas al lago',
                'descripcion_camas' => '1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 0,
                'precio_base' => 400000,
                'es_apartamento' => false,
                'metros_cuadrados' => 21,
                'amenidades' => json_encode(['Habitación', 'Balcón', 'Vistas al lago', 'Bañera de hidromasaje', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'Sauna', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Familiar con baño privado',
                'descripcion_camas' => '1 cama individual y 1 cama doble',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'precio_base' => 382500,
                'es_apartamento' => false,
                'metros_cuadrados' => 20,
                'amenidades' => json_encode(['Habitación', 'Vistas a la montaña', 'Vistas a un patio interior', 'Baño en la habitación', 'TV pantalla plana', 'Cafetera', 'WiFi gratis'])
            ],
            [
                'nombre' => 'Habitación Deluxe',
                'descripcion_camas' => '1 cama doble grande',
                'capacidad_adultos' => 2,
                'capacidad_ninos' => 1,
                'precio_base' => 450000,
                'es_apartamento' => false,
                'metros_cuadrados' => 25,
                'amenidades' => json_encode(['Habitación', 'Balcón', 'Vistas al lago', 'Vistas a la montaña', 'Vistas a un lugar de interés', 'Bañera de hidromasaje', 'Baño en la habitación', 'Cafetera', 'WiFi gratis'])
            ]
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_habitacions')->insert($tipo);
        }
    }
}
