<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\TenantContext;
use App\Filament\Support\Pages\CreateRecordPage;

class CreateUser extends CreateRecordPage
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->role !== 'superadmin') {
            $data['tenant_id'] = app(TenantContext::class)->tenantId();
        }

        return $data;
    }
}
