<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Services\AuditLogService;
use App\Services\DocumentPdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditInvoice extends EditRecord
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
            Action::make('generatePdf')
                ->label('Generate PDF')
                ->disabled(fn (): bool => (int) $this->record->status === 4)
                ->action(function (): void {
                    app(DocumentPdfService::class)->generateInvoice($this->record);
                    app(AuditLogService::class)->log('document_generate', 'invoice', $this->record);
                    Notification::make()->title('PDF generated')->success()->send();
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
