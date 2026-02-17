<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Customer;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use App\Services\TenantContext;
use App\Filament\Support\Pages\CreateRecordPage;
use Illuminate\Validation\ValidationException;

class CreatePayment extends CreateRecordPage
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = app(TenantContext::class)->tenantId() ?? auth()->user()?->tenant_id;

        if (empty($tenantId) && filled($data['customer_id'] ?? null)) {
            $tenantId = Customer::query()
                ->withoutGlobalScopes()
                ->whereKey($data['customer_id'])
                ->value('tenant_id');
        }

        if (empty($tenantId)) {
            throw ValidationException::withMessages([
                'customer_id' => 'Pilih customer terlebih dahulu agar tenant payment dapat ditentukan.',
            ]);
        }

        $data['tenant_id'] = $tenantId;
        $data['number'] = app(AccountingService::class)->nextNumber('payment', tenantId: $tenantId);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->log('create', 'payment', $this->record, null, $this->record->toArray());
    }
}
