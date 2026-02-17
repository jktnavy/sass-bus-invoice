<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Tenant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Tenant')
                    ->options(fn () => Tenant::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (): bool => auth()->user()?->role === 'superadmin'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
