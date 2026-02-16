<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Models\Tenant;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use App\Filament\Support\Pages\CreateRecordPage;

class CreateQuotation extends CreateRecordPage
{
    protected static string $resource = QuotationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
        $settings = is_array($tenant?->settings) ? $tenant->settings : [];

        $data['number'] = app(AccountingService::class)->nextNumber('quotation');
        $data['city'] ??= 'Jakarta';
        $data['attachment_text'] ??= '-';
        $data['subject_text'] ??= 'Penawaran Sewa Kendaraan';
        $data['fare_text_label'] ??= 'Harga sewa bus';
        $data['opening_paragraph'] ??= 'Kami dari PT. Sumber Tali Asih (STA Trans) dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:';
        $data['closing_paragraph'] ??= 'Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.';
        $data['signatory_name'] ??= $settings['signatory_name'] ?? auth()->user()?->name;
        $data['signatory_title'] ??= $settings['signatory_position'] ?? null;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->log('create', 'quotation', $this->record, null, $this->record->toArray());
    }
}
