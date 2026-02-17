<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

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
                TextColumn::make('payment.number')->label('Payment'),
                TextColumn::make('allocated_amount')->money('IDR'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->headerActions([])
            ->recordActions([]);
    }
}
