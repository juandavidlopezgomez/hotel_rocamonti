<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    /**
     * Listar actividades con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::with('user:id,name,email');

        // Filtro por usuario
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por acción
        if ($request->has('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->has('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Filtro por tipo de modelo
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Ordenar por más reciente
        $query->orderBy('created_at', 'desc');

        // Paginación
        $perPage = $request->get('per_page', 20);
        $activities = $query->paginate($perPage);

        return response()->json($activities);
    }

    /**
     * Ver detalle de una actividad
     */
    public function show(int $id): JsonResponse
    {
        $activity = Activity::with('user:id,name,email')->findOrFail($id);

        return response()->json([
            'activity' => $activity,
        ]);
    }

    /**
     * Estadísticas de actividades
     */
    public function stats(Request $request): JsonResponse
    {
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin = $request->get('fecha_fin', now()->toDateString());

        // Total de actividades en el período
        $totalActivities = Activity::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->count();

        // Actividades por acción
        $actividadesPorAccion = Activity::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Usuarios más activos
        $usuariosMasActivos = Activity::with('user:id,name,email')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'user' => $item->user,
                    'total' => $item->total,
                ];
            });

        // Actividad por día (últimos 30 días)
        $actividadPorDia = Activity::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Actividades recientes
        $actividadesRecientes = Activity::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'total_actividades' => $totalActivities,
            'actividades_por_accion' => $actividadesPorAccion,
            'usuarios_mas_activos' => $usuariosMasActivos,
            'actividad_por_dia' => $actividadPorDia,
            'actividades_recientes' => $actividadesRecientes,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}
