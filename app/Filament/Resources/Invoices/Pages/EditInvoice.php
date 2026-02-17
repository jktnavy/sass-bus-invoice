<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Services\AuditLogService;
use App\Services\DocumentShareUrlService;
use Filament\Actions\Action;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Support\Pages\EditRecordPage;
use Filament\Support\Exceptions\Halt;

class EditInvoice extends EditRecordPage
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markSent')
                ->label('Mark Sent')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->action(function (): void {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => 1]);
                    app(AuditLogService::class)->log('status_change', 'invoice', $this->record, $old, $this->record->toArray());
                }),
            Action::make('voidInvoice')
                ->label('Void')
                ->requiresConfirmation()
                ->color('danger')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->action(function (): void {
                    $old = $this->record->toArray();
                    $this->record->update(['status' => 4]);
                    app(AuditLogService::class)->log('void', 'invoice', $this->record, $old, $this->record->toArray());
                    Notification::make()->title('Invoice voided')->warning()->send();
                }),
            Action::make('previewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->url(fn (): string => route('invoices.pdf.preview', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->url(fn (): string => route('invoices.pdf.download', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('copyInvoiceShareLink')
                ->label('Copy Share Link Invoice')
                ->icon('heroicon-o-link')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->action(function (\Livewire\Component $livewire): void {
                    $url = app(DocumentShareUrlService::class)->invoice($this->record->id);
                    $encodedUrl = json_encode($url, JSON_UNESCAPED_SLASHES);

                    $livewire->js("navigator.clipboard?.writeText({$encodedUrl}).catch(() => {});");

                    Notification::make()
                        ->title('Share link invoice berhasil disalin')
                        ->body($url)
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Action::make('previewReceipt')
                ->label('Preview Kwitansi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->visible(fn (): bool => ((float) $this->record->balance_total <= 0 || (int) $this->record->status === 3) && (int) $this->record->status !== 4)
                ->url(fn (): string => route('invoices.receipt.preview', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('downloadReceipt')
                ->label('Download Kwitansi')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn (): bool => ((float) $this->record->balance_total <= 0 || (int) $this->record->status === 3) && (int) $this->record->status !== 4)
                ->url(fn (): string => route('invoices.receipt.download', ['id' => $this->record->id]))
                ->openUrlInNewTab(),
            Action::make('copyReceiptShareLink')
                ->label('Copy Share Link Kwitansi')
                ->icon('heroicon-o-link')
                ->visible(fn (): bool => ((float) $this->record->balance_total <= 0 || (int) $this->record->status === 3) && (int) $this->record->status !== 4)
                ->action(function (\Livewire\Component $livewire): void {
                    $url = app(DocumentShareUrlService::class)->receipt($this->record->id);
                    $encodedUrl = json_encode($url, JSON_UNESCAPED_SLASHES);

                    $livewire->js("navigator.clipboard?.writeText({$encodedUrl}).catch(() => {});");

                    Notification::make()
                        ->title('Share link kwitansi berhasil disalin')
                        ->body($url)
                        ->success()
                        ->persistent()
                        ->send();
                }),
            DeleteAction::make()->visible(false),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->disabled(fn (): bool => (int) $this->record->status === 4)
            ->tooltip('Invoice berstatus Void tidak dapat diubah.');
    }

    protected function beforeSave(): void
    {
        if ((int) $this->record->status !== 4) {
            return;
        }

        Notification::make()
            ->title('Invoice berstatus Void tidak dapat diubah.')
            ->danger()
            ->send();

        throw new Halt();
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->log('update', 'invoice', $this->record, null, $this->record->toArray());
    }
}
