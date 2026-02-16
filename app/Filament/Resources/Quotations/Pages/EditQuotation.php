<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Quotations\QuotationResource;
use App\Models\Document;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use App\Services\DocumentPdfService;
use Throwable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
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
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('open')
                                ->label('Open Invoice')
                                ->url(InvoiceResource::getUrl('edit', ['record' => $result['invoice_id']]))
                                ->button(),
                        ])
                        ->send();
                }),
            Action::make('createInvoiceDraft')
                ->label('Create Invoice Draft (Editable)')
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => InvoiceResource::getUrl('create', [
                    'source_quotation_id' => $this->record->id,
                ])),
            Action::make('generatePdf')
                ->label('Generate PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function (): void {
                    try {
                        $document = app(DocumentPdfService::class)->generateQuotation($this->record);
                        app(AuditLogService::class)->log('document_generate', 'quotation', $this->record, null, [
                            'document_id' => $document->id,
                        ]);

                        Notification::make()
                            ->title('PDF quotation berhasil dibuat')
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('open')
                                    ->label('Open PDF')
                                    ->url(route('documents.open', ['id' => $document->id]))
                                    ->button(),
                                \Filament\Notifications\Actions\Action::make('download')
                                    ->label('Download PDF')
                                    ->url(route('documents.download', ['id' => $document->id]))
                                    ->button(),
                            ])
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Generate PDF gagal')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->visible(fn (): bool => Document::query()
                    ->where('owner_table', 'quotations')
                    ->where('owner_id', $this->record->id)
                    ->exists())
                ->url(function (): string {
                    $document = Document::query()
                        ->where('owner_table', 'quotations')
                        ->where('owner_id', $this->record->id)
                        ->latest('created_at')
                        ->firstOrFail();

                    return route('documents.open', ['id' => $document->id]);
                })
                ->openUrlInNewTab(),
            DeleteAction::make()->visible(false),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->log('update', 'quotation', $this->record, null, $this->record->toArray());
    }
}
