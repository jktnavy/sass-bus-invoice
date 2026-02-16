<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Quotations\QuotationResource;
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
}
