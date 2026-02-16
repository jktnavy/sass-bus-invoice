<?php

namespace App\Filament\Resources\Payments\RelationManagers;

use App\Models\Invoice;
use App\Services\AccountingService;
use Filament\Actions\Action;
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
                    ->form([
                        Select::make('invoice_id')
                            ->label('Invoice')
                            ->options(fn () => Invoice::query()->whereIn('status', [1, 2])->pluck('number', 'id')->all())
                            ->required()
                            ->searchable(),
                        TextInput::make('amount')->numeric()->required(),
                    ])
                    ->action(function (array $data): void {
                        $invoice = Invoice::query()->findOrFail($data['invoice_id']);
                        app(AccountingService::class)->allocatePayment($this->getOwnerRecord(), $invoice, (float) $data['amount']);
                        $this->getOwnerRecord()->refresh();
                        Notification::make()->title('Allocation saved')->success()->send();
                    }),
            ])
            ->recordActions([]);
    }
}
