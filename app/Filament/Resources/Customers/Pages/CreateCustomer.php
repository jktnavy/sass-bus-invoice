<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Services\TenantContext;
use App\Filament\Support\Pages\CreateRecordPage;
use Illuminate\Validation\ValidationException;

class CreateCustomer extends CreateRecordPage
{
    protected static string $resource = CustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->role === 'superadmin') {
            if (empty($data['tenant_id'])) {
                throw ValidationException::withMessages([
                    'tenant_id' => 'Tenant wajib dipilih untuk membuat customer.',
                ]);
            }

            return $data;
        }

        $tenantId = app(TenantContext::class)->tenantId();

        if (empty($tenantId)) {
            throw ValidationException::withMessages([
                'data' => 'Akun Anda belum terhubung ke tenant. Hubungi superadmin.',
            ]);
        }

        $data['tenant_id'] = $tenantId;

        return $data;
    }
}
