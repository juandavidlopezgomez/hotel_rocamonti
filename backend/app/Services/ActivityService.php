<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityService
{
    /**
     * Registrar una actividad en el sistema
     *
     * @param string $action Acción realizada (ej: 'created_reservation')
     * @param string|null $modelType Clase del modelo afectado
     * @param int|null $modelId ID del registro afectado
     * @param array $details Detalles adicionales
     * @return Activity
     */
    public function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        array $details = []
    ): Activity {
        return Activity::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Atajos para acciones comunes
     */
    public function logCreated(string $modelType, int $modelId, array $details = []): Activity
    {
        return $this->log(
            "created_{$this->getModelName($modelType)}",
            $modelType,
            $modelId,
            $details
        );
    }

    public function logUpdated(string $modelType, int $modelId, array $details = []): Activity
    {
        return $this->log(
            "updated_{$this->getModelName($modelType)}",
            $modelType,
            $modelId,
            $details
        );
    }

    public function logDeleted(string $modelType, int $modelId, array $details = []): Activity
    {
        return $this->log(
            "deleted_{$this->getModelName($modelType)}",
            $modelType,
            $modelId,
            $details
        );
    }

    /**
     * Obtener el nombre simple del modelo
     */
    private function getModelName(string $modelType): string
    {
        $parts = explode('\\', $modelType);
        return strtolower(end($parts));
    }
}
