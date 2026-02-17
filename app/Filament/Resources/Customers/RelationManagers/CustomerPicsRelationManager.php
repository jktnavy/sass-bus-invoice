<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\CreateAction;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Filament\Support\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerPicsRelationManager extends RelationManager
{
    protected static string $relationship = 'pics';

    protected static ?string $title = 'PIC Customers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('PIC Name')
                ->required()
                ->maxLength(150),
            TextInput::make('phone')
                ->label('PIC Phone')
                ->maxLength(30),
            TextInput::make('email')
                ->label('PIC Email')
                ->email()
                ->maxLength(255),
            TextInput::make('position')
                ->label('Position')
                ->maxLength(100),
            Select::make('is_primary')
                ->label('Primary PIC')
                ->options([1 => 'Yes', 0 => 'No'])
                ->default(0)
                ->required(),
            TextInput::make('notes')
                ->label('Notes')
                ->maxLength(500),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('PIC Name')->searchable(),
                TextColumn::make('phone')->label('PIC Phone'),
                TextColumn::make('email')->label('PIC Email'),
                TextColumn::make('position'),
                IconColumn::make('is_primary')->label('Primary')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['tenant_id'] = $this->getOwnerRecord()->tenant_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
