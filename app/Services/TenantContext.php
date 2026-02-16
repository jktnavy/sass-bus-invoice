<?php

namespace App\Services;

use App\Models\User;

class TenantContext
{
    private ?string $tenantId = null;

    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function setFromUser(?User $user): void
    {
        $this->tenantId = $user?->tenant_id;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function requireTenantId(): string
    {
        if (! $this->tenantId) {
            throw new \RuntimeException('Tenant context is not set.');
        }

        return $this->tenantId;
    }
}
