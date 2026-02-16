<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Services\AccountingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('date')->date(),
                TextColumn::make('due_date')->date(),
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ((int) $state) {
                        0 => 'Draft',
                        1 => 'Sent',
                        2 => 'Partial',
                        3 => 'Paid',
                        4 => 'Void',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ((int) $state) {
                        0 => 'gray',
                        1 => 'info',
                        2 => 'warning',
                        3 => 'success',
                        4 => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('grand_total')->money('IDR'),
                TextColumn::make('balance_total')->money('IDR'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    0 => 'Draft',
                    1 => 'Sent',
                    2 => 'Partial',
                    3 => 'Paid',
                    4 => 'Void',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('clone')
                    ->label('Clone Record')
                    ->icon('heroicon-o-squares-plus')
                    ->action(function ($record): void {
                        DB::transaction(function () use ($record): void {
                            $new = $record->replicate();
                            $new->number = app(AccountingService::class)->nextNumber('invoice');
                            $new->status = 0;
                            $new->paid_total = 0;
                            $new->save();

                            foreach ($record->items as $item) {
                                $newItem = $item->replicate();
                                $newItem->invoice_id = $new->id;
                                $newItem->save();
                            }
                        });
                    }),
                Action::make('openPdf')
                    ->label('Open PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => route('invoices.pdf.preview', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => route('invoices.pdf.download', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('openReceipt')
                    ->label('Open Kwitansi')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->visible(fn ($record): bool => ((float) $record->balance_total <= 0 || (int) $record->status === 3) && (int) $record->status !== 4)
                    ->url(fn ($record): string => route('invoices.receipt.preview', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('downloadReceipt')
                    ->label('Download Kwitansi')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn ($record): bool => ((float) $record->balance_total <= 0 || (int) $record->status === 3) && (int) $record->status !== 4)
                    ->url(fn ($record): string => route('invoices.receipt.download', ['id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([]);
    }
}
