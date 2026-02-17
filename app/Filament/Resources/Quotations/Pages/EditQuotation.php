<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Quotations\QuotationResource;
use App\Filament\Resources\Quotations\Schemas\QuotationForm;
use App\Models\Tenant;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use Filament\Actions\Action;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Support\Pages\EditRecordPage;

class EditQuotation extends EditRecordPage
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAccepted')
                ->label('Mark Accepted')
                ->action(function (): void {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => 2]);
                    app(AuditLogService::class)->log('status_change', 'quotation', $this->record, $old, $this->record->toArray());
                }),
            Action::make('convertToInvoice')
                ->label('Convert to Invoice')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('due_date')->required(),
                ])
                ->action(function (array $data): void {
                    $result = app(AccountingService::class)->convertQuotationToInvoice($this->record, \Carbon\Carbon::parse($data['due_date']));
                    app(AuditLogService::class)->log('convert', 'quotation', $this->record, null, ['invoice_id' => $result['invoice_id']]);

                    Notification::make()
                        ->title('Invoice created: '.$result['invoice_number'])
                        ->success()
                        ->body('Invoice berhasil dibuat. Buka dari menu Invoices.')
                        ->send();
                }),
            Action::make('createInvoiceDraft')
                ->label('Create Invoice Draft (Editable)')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => InvoiceResource::getUrl('create', [
                    'source_quotation_id' => $this->record->id,
                ])),
            Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->url(fn (): string => route('quotations.pdf.preview', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('quotations.pdf.download', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            DeleteAction::make()->visible(false),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->log('update', 'quotation', $this->record, null, $this->record->toArray());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
        $settings = is_array($tenant?->settings) ? $tenant->settings : [];

        $data['subject_text'] ??= 'Penawaran Sewa Kendaraan';
        $data['opening_paragraph'] = filled($data['opening_paragraph'] ?? null) ? $data['opening_paragraph'] : QuotationForm::defaultOpeningParagraph();
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
}
