<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = app(AccountingService::class)->nextNumber('payment');

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->log('create', 'payment', $this->record, null, $this->record->toArray());
    }
}
