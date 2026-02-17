<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use App\Support\FriendlyExceptionMessage;
use Filament\Actions\Action;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use Filament\Notifications\Notification;
use App\Filament\Support\Pages\EditRecordPage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EditPayment extends EditRecordPage
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
                    try {
                        $hasAllocations = $this->record->allocations()->exists();

                        if ($hasAllocations) {
                            Notification::make()
                                ->title('Reverse tidak dapat diproses')
                                ->body('Payment ini sudah memiliki allocation ke invoice. Reverse hanya bisa dilakukan untuk payment tanpa allocation.')
                                ->danger()
                                ->send();

                            return;
                        }

                        app(AccountingService::class)->reversePayment($this->record);
                        $this->record->refresh();
                        app(AuditLogService::class)->log('reverse', 'payment', $this->record);
                        Notification::make()->title('Payment reversed')->warning()->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Reverse gagal')
                            ->body(FriendlyExceptionMessage::from($exception))
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make()->visible(false),
        ];
    }

    protected function afterSave(): void
    {
        app(AuditLogService::class)->log('update', 'payment', $this->record, null, $this->record->toArray());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentStatus = (int) ($this->record->status ?? 0);

        // Payment yang sudah posted/reversed dianggap final untuk field inti.
        if ($currentStatus !== 0) {
            $lockedFields = ['date', 'customer_id', 'method', 'amount'];

            foreach ($lockedFields as $field) {
                $old = (string) ($this->record->{$field} ?? '');
                $new = (string) ($data[$field] ?? '');

                if ($old !== $new) {
                    throw ValidationException::withMessages([
                        $field => 'Payment yang sudah Posted/Reversed tidak boleh mengubah tanggal, customer, metode, atau amount.',
                    ]);
                }
            }
        }

        return $data;
    }
}
