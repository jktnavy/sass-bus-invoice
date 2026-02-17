<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Services\TenantBrandingService;
use App\Filament\Support\Pages\CreateRecordPage;

class CreateTenant extends CreateRecordPage
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['settings'] = [
            'company_logo_path' => $data['company_logo_path'] ?? null,
            'stamp_logo_path' => $data['stamp_logo_path'] ?? null,
            'signature_path' => $data['signature_path'] ?? null,
            'signatory_name' => $data['signatory_name'] ?? null,
            'signatory_position' => $data['signatory_position'] ?? null,
            'company_website' => $data['company_website'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'document_notes' => $data['document_notes'] ?? null,
        ];

        unset(
            $data['company_logo_path'],
            $data['stamp_logo_path'],
            $data['signature_path'],
            $data['signatory_name'],
            $data['signatory_position'],
            $data['company_website'],
            $data['bank_name'],
            $data['bank_account_holder'],
            $data['bank_account_number'],
            $data['document_notes'],
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        app(TenantBrandingService::class)->refreshMergedStampSignature($this->record);
    }
}
