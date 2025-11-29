<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Reservas
            'view-reservations',
            'create-reservation',
            'edit-reservation',
            'delete-reservation',
            'checkin-checkout',

            // Habitaciones
            'view-rooms',
            'edit-room-status',
            'manage-pricing',
            'manage-cleaning',
            'manage-maintenance',

            // Clientes
            'view-clients',
            'edit-client',
            'delete-client',

            // Reportes
            'view-reports',
            'export-reports',

            // Configuración
            'manage-settings',

            // Usuarios y Roles
            'manage-users',
            'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos

        // 1. Super Admin - Acceso total
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Gerente - Dashboard, reportes, gestión de reservas, habitaciones y clientes
        $gerente = Role::create(['name' => 'Gerente']);
        $gerente->givePermissionTo([
            'view-dashboard',
            'view-reservations',
            'create-reservation',
            'edit-reservation',
            'delete-reservation',
            'checkin-checkout',
            'view-rooms',
            'edit-room-status',
            'manage-pricing',
            'manage-cleaning',
            'manage-maintenance',
            'view-clients',
            'edit-client',
            'delete-client',
            'view-reports',
            'export-reports',
        ]);

        // 3. Recepcionista - Check-in/out, crear/modificar reservas, consultar disponibilidad
        $recepcionista = Role::create(['name' => 'Recepcionista']);
        $recepcionista->givePermissionTo([
            'view-dashboard',
            'view-reservations',
            'create-reservation',
            'edit-reservation',
            'checkin-checkout',
            'view-rooms',
            'view-clients',
            'edit-client',
        ]);

        // 4. Housekeeping - Ver habitaciones, cambiar estado de limpieza, reportar incidencias
        $housekeeping = Role::create(['name' => 'Housekeeping']);
        $housekeeping->givePermissionTo([
            'view-rooms',
            'edit-room-status',
            'manage-cleaning',
        ]);

        // Crear usuario Super Admin de prueba
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@hotelrocamonti.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);
        $admin->assignRole('Super Admin');

        // Crear usuario Gerente de prueba
        $gerente_user = User::create([
            'name' => 'Gerente Hotel',
            'email' => 'gerente@hotelrocamonti.com',
            'password' => Hash::make('gerente123'),
            'is_admin' => true,
        ]);
        $gerente_user->assignRole('Gerente');

        // Crear usuario Recepcionista de prueba
        $recepcionista_user = User::create([
            'name' => 'Recepcionista',
            'email' => 'recepcion@hotelrocamonti.com',
            'password' => Hash::make('recepcion123'),
            'is_admin' => true,
        ]);
        $recepcionista_user->assignRole('Recepcionista');

        // Crear usuario Housekeeping de prueba
        $housekeeping_user = User::create([
            'name' => 'Personal Limpieza',
            'email' => 'limpieza@hotelrocamonti.com',
            'password' => Hash::make('limpieza123'),
            'is_admin' => false,
        ]);
        $housekeeping_user->assignRole('Housekeeping');

        $this->command->info('Roles y permisos creados exitosamente!');
        $this->command->info('');
        $this->command->info('Usuarios creados:');
        $this->command->info('Super Admin: admin@hotelrocamonti.com / admin123');
        $this->command->info('Gerente: gerente@hotelrocamonti.com / gerente123');
        $this->command->info('Recepcionista: recepcion@hotelrocamonti.com / recepcion123');
        $this->command->info('Housekeeping: limpieza@hotelrocamonti.com / limpieza123');
    }
}
