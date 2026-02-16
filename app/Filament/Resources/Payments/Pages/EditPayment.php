<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post')
                ->visible(fn () => (int) $this->record->status === 0)
                ->action(function (): void {
                    app(AccountingService::class)->postPayment($this->record);
                    $this->record->refresh();
                    app(AuditLogService::class)->log('post', 'payment', $this->record);
                    Notification::make()->title('Payment posted')->success()->send();
                }),
            Action::make('reverse')
                ->label('Reverse')
                ->color('danger')
                ->visible(fn () => (int) $this->record->status === 1)
                ->action(function (): void {
                    app(AccountingService::class)->reversePayment($this->record);
                    $this->record->refresh();
                    app(AuditLogService::class)->log('reverse', 'payment', $this->record);
                    Notification::make()->title('Payment reversed')->warning()->send();
                }),
            DeleteAction::make()->visible(false),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->log('update', 'payment', $this->record, null, $this->record->toArray());
    }
}
