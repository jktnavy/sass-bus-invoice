<?php

namespace App\Filament\Resources\Quotations\Tables;

use App\Models\Document;
use App\Services\AccountingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class QuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('date')->date(),
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('status'),
                TextColumn::make('grand_total')->money('IDR'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    0 => 'Draft',
                    1 => 'Sent',
                    2 => 'Accepted',
                    3 => 'Rejected',
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
                            $new->number = app(AccountingService::class)->nextNumber('quotation');
                            $new->status = 0;
                            $new->save();

                            foreach ($record->items as $item) {
                                $newItem = $item->replicate();
                                $newItem->quotation_id = $new->id;
                                $newItem->save();
                            }
                        });
                    }),
                Action::make('openPdf')
                    ->label('Open PDF')
                    ->icon('heroicon-o-eye')
                    ->visible(fn ($record): bool => Document::query()
                        ->where('owner_table', 'quotations')
                        ->where('owner_id', $record->id)
                        ->exists())
                    ->url(function ($record): string {
                        $document = Document::query()
                            ->where('owner_table', 'quotations')
                            ->where('owner_id', $record->id)
                            ->latest('created_at')
                            ->firstOrFail();

                        return route('documents.open', ['id' => $document->id]);
                    })
                    ->openUrlInNewTab(),
                Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn ($record): bool => Document::query()
                        ->where('owner_table', 'quotations')
                        ->where('owner_id', $record->id)
                        ->exists())
                    ->url(function ($record): string {
                        $document = Document::query()
                            ->where('owner_table', 'quotations')
                            ->where('owner_id', $record->id)
                            ->latest('created_at')
                            ->firstOrFail();

                        return route('documents.download', ['id' => $document->id]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([]);
    }
}
