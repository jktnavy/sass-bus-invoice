<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(string $action, string $entity, ?Model $entityModel = null, mixed $oldData = null, mixed $newData = null): void
    {
        $user = Auth::user();

        AuditLog::create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityModel?->getKey(),
            'old_data' => $oldData ? json_encode($oldData, JSON_THROW_ON_ERROR) : null,
            'new_data' => $newData ? json_encode($newData, JSON_THROW_ON_ERROR) : null,
        ]);
    }
}
