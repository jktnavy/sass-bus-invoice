<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Filament\Resources\Quotations\Schemas\QuotationForm;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use App\Services\TenantContext;
use App\Filament\Support\Pages\CreateRecordPage;
use Illuminate\Validation\ValidationException;

class CreateQuotation extends CreateRecordPage
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = app(TenantContext::class)->tenantId() ?? auth()->user()?->tenant_id;

        if (auth()->user()?->role === 'superadmin') {
            $tenantId = $data['tenant_id'] ?? $tenantId;

            if (empty($tenantId) && filled($data['customer_id'] ?? null)) {
                $tenantId = Customer::query()
                    ->withoutGlobalScopes()
                    ->whereKey($data['customer_id'])
                    ->value('tenant_id');
            }
        }

        if (empty($tenantId)) {
            throw ValidationException::withMessages([
                'tenant_id' => 'Tenant wajib dipilih untuk membuat quotation.',
            ]);
        }

        $data['tenant_id'] = $tenantId;

        $tenant = Tenant::query()->find($tenantId);
        $settings = is_array($tenant?->settings) ? $tenant->settings : [];

        $data['number'] = app(AccountingService::class)->nextNumber('quotation', tenantId: $tenantId);
        $data['city'] ??= 'Jakarta';
        $data['attachment_text'] ??= '-';
        $data['subject_text'] ??= 'Penawaran Sewa Kendaraan';
        $data['fare_text_label'] ??= 'Harga sewa bus';
        $data['opening_paragraph'] ??= QuotationForm::defaultOpeningParagraph();
        $data['closing_paragraph'] ??= 'Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.';
        $data['usage_end_date'] = filled($data['usage_end_date'] ?? null) ? $data['usage_end_date'] : ($data['usage_date'] ?? null);
        $data['included_text'] = filled($data['included_text'] ?? null) ? $data['included_text'] : null;
        $data['excluded_text'] = filled($data['excluded_text'] ?? null) ? $data['excluded_text'] : null;
        $data['facilities_text'] = filled($data['facilities_text'] ?? null) ? $data['facilities_text'] : 'AC, TV, Karaoke, Reclining Seats';
        $data['payment_method_text'] = QuotationForm::defaultPaymentMethodText();
        $data['signatory_name'] = $settings['signatory_name'] ?? auth()->user()?->name;
        $data['signatory_title'] = $settings['signatory_position'] ?? null;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->log('create', 'quotation', $this->record, null, $this->record->toArray());
    }
}
