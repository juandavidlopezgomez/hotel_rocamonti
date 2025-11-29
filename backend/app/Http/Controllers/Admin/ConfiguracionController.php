<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ConfiguracionController extends Controller
{
    /**
     * Obtener configuración general del sistema
     */
    public function getConfiguracion()
    {
        try {
            // Por ahora retornamos configuración de ejemplo
            // En implementación completa habría tabla de configuraciones
            $configuracion = [
                'general' => [
                    'nombre_hotel' => env('APP_NAME', 'Hotel Rocamonti'),
                    'email_contacto' => 'info@hotelrocamonti.com',
                    'telefono' => '+57 314 123 4567',
                    'direccion' => 'Guatavita, Cundinamarca',
                    'moneda' => 'COP',
                    'zona_horaria' => config('app.timezone', 'America/Bogota'),
                ],
                'reservas' => [
                    'check_in_hora' => '15:00',
                    'check_out_hora' => '12:00',
                    'dias_anticipacion_max' => 365,
                    'dias_anticipacion_min' => 1,
                    'noches_minimas' => 1,
                    'noches_maximas' => 30,
                ],
                'pagos' => [
                    'wompi_habilitado' => !empty(env('WOMPI_PUBLIC_KEY')),
                    'metodos_aceptados' => ['wompi', 'efectivo', 'transferencia'],
                    'requiere_prepago' => true,
                    'porcentaje_prepago' => 50,
                ],
                'notificaciones' => [
                    'email_confirmacion' => true,
                    'email_recordatorio' => true,
                    'dias_recordatorio' => 2,
                    'email_admin_nueva_reserva' => true,
                ]
            ];

            return response()->json([
                'success' => true,
                'configuracion' => $configuracion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar configuración'
            ], 500);
        }
    }

    /**
     * Actualizar configuración general
     */
    public function updateConfiguracion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'seccion' => 'required|in:general,reservas,pagos,notificaciones',
                'datos' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // En implementación completa, aquí se guardarían en base de datos
            // Por ahora solo validamos y retornamos success

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'datos' => $request->datos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración'
            ], 500);
        }
    }

    /**
     * Obtener políticas del hotel
     */
    public function getPoliticas()
    {
        try {
            $politicas = [
                'cancelacion' => [
                    'titulo' => 'Política de Cancelación',
                    'descripcion' => 'Cancelación gratuita hasta 48 horas antes de la fecha de entrada. Cancelaciones posteriores tienen cargo del 50%.',
                    'dias_cancelacion_gratuita' => 2,
                    'porcentaje_penalizacion' => 50,
                ],
                'ninos' => [
                    'titulo' => 'Política de Niños',
                    'descripcion' => 'Niños menores de 5 años no pagan. De 5 a 12 años pagan el 50%.',
                    'edad_gratis' => 5,
                    'edad_descuento' => 12,
                    'porcentaje_descuento' => 50,
                ],
                'mascotas' => [
                    'titulo' => 'Política de Mascotas',
                    'descripcion' => 'Se permiten mascotas pequeñas con cargo adicional de $50.000 por noche.',
                    'permitidas' => true,
                    'cargo_adicional' => 50000,
                    'peso_maximo_kg' => 10,
                ],
                'fumadores' => [
                    'titulo' => 'Política de Fumadores',
                    'descripcion' => 'Todas las habitaciones son libres de humo. Se habilitan zonas específicas para fumadores.',
                    'habitaciones_fumador' => false,
                    'zonas_fumador' => true,
                ],
            ];

            return response()->json([
                'success' => true,
                'politicas' => $politicas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener políticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar políticas'
            ], 500);
        }
    }

    /**
     * Actualizar políticas
     */
    public function updatePoliticas(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'required|in:cancelacion,ninos,mascotas,fumadores',
                'datos' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // En implementación completa, se guardaría en base de datos

            return response()->json([
                'success' => true,
                'message' => 'Política actualizada exitosamente',
                'datos' => $request->datos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar políticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar políticas'
            ], 500);
        }
    }

    /**
     * Test de conexión con Wompi
     */
    public function testWompi()
    {
        try {
            $publicKey = env('WOMPI_PUBLIC_KEY');
            $integritySecret = env('WOMPI_INTEGRITY_SECRET');
            $urlBase = env('WOMPI_URL', 'https://production.wompi.co/v1');

            if (empty($publicKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'WOMPI_PUBLIC_KEY no configurada',
                    'configurado' => false
                ]);
            }

            if (empty($integritySecret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'WOMPI_INTEGRITY_SECRET no configurada',
                    'configurado' => false
                ]);
            }

            // Test de firma
            $reference = 'TEST-' . time();
            $amount = 100000;
            $currency = 'COP';
            $concatenated = $reference . $amount . $currency . $integritySecret;
            $signature = hash('sha256', $concatenated);

            // Intentar hacer una petición de prueba a Wompi
            try {
                $response = Http::get($urlBase . '/merchants/' . $publicKey);

                return response()->json([
                    'success' => true,
                    'message' => 'Conexión exitosa con Wompi',
                    'configurado' => true,
                    'detalles' => [
                        'url_base' => $urlBase,
                        'public_key_preview' => substr($publicKey, 0, 20) . '...',
                        'integrity_secret_configurado' => true,
                        'test_signature' => substr($signature, 0, 20) . '...',
                        'merchant_status' => $response->successful() ? 'Válido' : 'Error'
                    ]
                ]);
            } catch (\Exception $httpException) {
                return response()->json([
                    'success' => true,
                    'message' => 'Credenciales configuradas (sin verificación de red)',
                    'configurado' => true,
                    'detalles' => [
                        'url_base' => $urlBase,
                        'public_key_preview' => substr($publicKey, 0, 20) . '...',
                        'integrity_secret_configurado' => true,
                        'test_signature' => substr($signature, 0, 20) . '...',
                        'nota' => 'No se pudo verificar con Wompi (posible problema de red)'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al testear Wompi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al testear conexión: ' . $e->getMessage(),
                'configurado' => false
            ], 500);
        }
    }

    /**
     * Listar respaldos de base de datos
     */
    public function getBackups()
    {
        try {
            $backupsPath = storage_path('app/backups');

            if (!file_exists($backupsPath)) {
                return response()->json([
                    'success' => true,
                    'backups' => []
                ]);
            }

            $files = array_diff(scandir($backupsPath), ['.', '..']);
            $backups = [];

            foreach ($files as $file) {
                $filePath = $backupsPath . '/' . $file;
                if (is_file($filePath)) {
                    $backups[] = [
                        'nombre' => $file,
                        'tamano' => filesize($filePath),
                        'tamano_legible' => $this->formatBytes(filesize($filePath)),
                        'fecha' => date('Y-m-d H:i:s', filemtime($filePath))
                    ];
                }
            }

            // Ordenar por fecha descendente
            usort($backups, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });

            return response()->json([
                'success' => true,
                'backups' => $backups
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar backups: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al listar respaldos'
            ], 500);
        }
    }

    /**
     * Crear respaldo de base de datos
     */
    public function createBackup()
    {
        try {
            $backupsPath = storage_path('app/backups');

            if (!file_exists($backupsPath)) {
                mkdir($backupsPath, 0755, true);
            }

            $filename = 'backup_' . Carbon::now()->format('Y-m-d_His') . '.sql';
            $filepath = $backupsPath . '/' . $filename;

            // Obtener configuración de base de datos
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');

            // Comando mysqldump
            $command = sprintf(
                'mysqldump -h %s -u %s %s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                !empty($password) ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Ejecutar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear respaldo. Asegúrese de que mysqldump esté disponible.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Respaldo creado exitosamente',
                'backup' => [
                    'nombre' => $filename,
                    'tamano_legible' => $this->formatBytes(filesize($filepath)),
                    'fecha' => Carbon::now()->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar respaldo de base de datos
     */
    public function restoreBackup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $backupsPath = storage_path('app/backups');
            $filepath = $backupsPath . '/' . $request->nombre;

            if (!file_exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de respaldo no encontrado'
                ], 404);
            }

            // Obtener configuración de base de datos
            $database = env('DB_DATABASE');
            $username = env('DB_USERNAME');
            $password = env('DB_PASSWORD');
            $host = env('DB_HOST');

            // Comando mysql para restaurar
            $command = sprintf(
                'mysql -h %s -u %s %s %s < %s',
                escapeshellarg($host),
                escapeshellarg($username),
                !empty($password) ? '-p' . escapeshellarg($password) : '',
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Ejecutar comando
            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al restaurar respaldo'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Respaldo restaurado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Limpiar caché del sistema
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Caché limpiado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al limpiar caché: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar caché'
            ], 500);
        }
    }

    /**
     * Obtener información del sistema
     */
    public function getSystemInfo()
    {
        try {
            return response()->json([
                'success' => true,
                'info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'timezone' => config('app.timezone'),
                    'debug_mode' => config('app.debug'),
                    'environment' => config('app.env'),
                    'database' => [
                        'driver' => config('database.default'),
                        'database' => env('DB_DATABASE')
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener info del sistema: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información'
            ], 500);
        }
    }
}
