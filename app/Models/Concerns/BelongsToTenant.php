<?php

namespace App\Models\Concerns;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = app(TenantContext::class)->tenantId();

            if ($tenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $tenantId);
            }
        });

        static::creating(function ($model): void {
            $tenantId = app(TenantContext::class)->tenantId();

            if ($tenantId && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });

        static::saving(function ($model): void {
            $tenantId = app(TenantContext::class)->tenantId();

            if ($tenantId && isset($model->tenant_id) && $model->tenant_id !== $tenantId) {
                throw new \RuntimeException('Cross-tenant write prevented.');
            }
        });
    }
}
