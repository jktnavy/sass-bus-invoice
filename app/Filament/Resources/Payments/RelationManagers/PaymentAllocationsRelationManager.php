<?php

namespace App\Filament\Resources\Payments\RelationManagers;

use App\Models\Invoice;
use App\Services\AccountingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Support\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentAllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.number')->label('Invoice'),
                TextColumn::make('allocated_amount')->money('IDR'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([
                Action::make('allocate')
                    ->label('Allocate')
                    ->visible(fn (): bool => (int) $this->getOwnerRecord()->status === 1 && (float) $this->getOwnerRecord()->unapplied_amount > 0)
                    ->form([
                        Placeholder::make('unapplied_amount_info')
                            ->label('Sisa Belum Dialokasikan')
                            ->content(fn (): string => number_format((float) $this->getOwnerRecord()->unapplied_amount, 2, ',', '.')),
                        Select::make('invoice_id')
                            ->label('Invoice')
                            ->options(function (): array {
                                $payment = $this->getOwnerRecord();

                                return Invoice::query()
                                    ->where('customer_id', $payment->customer_id)
                                    ->whereIn('status', [1, 2])
                                    ->where('balance_total', '>', 0)
                                    ->orderByDesc('date')
                                    ->get()
                                    ->mapWithKeys(fn (Invoice $invoice) => [
                                        $invoice->id => sprintf(
                                            '%s | Balance: %s',
                                            $invoice->number,
                                            number_format((float) $invoice->balance_total, 2, ',', '.')
                                        ),
                                    ])
                                    ->all();
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Hanya invoice customer yang sama dan masih memiliki saldo.'),
                        TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->helperText('Nominal alokasi tidak boleh melebihi sisa payment dan sisa invoice.'),
                    ])
                    ->action(function (array $data): void {
                        $payment = $this->getOwnerRecord()->refresh();

                        if ((int) $payment->status !== 1) {
                            Notification::make()->title('Allocation gagal')->body('Payment harus berstatus Posted.')->danger()->send();

                            return;
                        }

                        $invoice = Invoice::query()->findOrFail($data['invoice_id']);
                        $amount = (float) $data['amount'];

                        if ($invoice->customer_id !== $payment->customer_id) {
                            Notification::make()->title('Allocation gagal')->body('Invoice harus milik customer yang sama dengan payment.')->danger()->send();

                            return;
                        }

                        if ((float) $payment->unapplied_amount <= 0) {
                            Notification::make()->title('Allocation gagal')->body('Tidak ada sisa payment yang bisa dialokasikan.')->danger()->send();

                            return;
                        }

                        if ($amount > (float) $payment->unapplied_amount) {
                            Notification::make()->title('Allocation gagal')->body('Nominal melebihi sisa payment (unapplied).')->danger()->send();

                            return;
                        }

                        if ($amount > (float) $invoice->balance_total) {
                            Notification::make()->title('Allocation gagal')->body('Nominal melebihi sisa tagihan invoice.')->danger()->send();

                            return;
                        }

                        app(AccountingService::class)->allocatePayment($payment, $invoice, $amount);
                        $this->getOwnerRecord()->refresh();
                        Notification::make()->title('Allocation saved')->success()->send();
                    }),
            ])
            ->recordActions([]);
    }
}
