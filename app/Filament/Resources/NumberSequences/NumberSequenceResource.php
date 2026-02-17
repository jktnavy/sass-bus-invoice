<?php

namespace App\Filament\Resources\NumberSequences;

use App\Filament\Resources\NumberSequences\Pages\CreateNumberSequence;
use App\Filament\Resources\NumberSequences\Pages\EditNumberSequence;
use App\Filament\Resources\NumberSequences\Pages\ListNumberSequences;
use App\Filament\Resources\NumberSequences\Schemas\NumberSequenceForm;
use App\Filament\Resources\NumberSequences\Tables\NumberSequencesTable;
use App\Models\NumberSequence;
use App\Support\RoleHelper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NumberSequenceResource extends Resource
{
    protected static ?string $model = NumberSequence::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    public static function canAccess(): bool
    {
        return RoleHelper::hasAnyRole(auth()->user(), ['admin']);
    }

    public static function form(Schema $schema): Schema
    {
        return NumberSequenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NumberSequencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNumberSequences::route('/'),
            'create' => CreateNumberSequence::route('/create'),
            'edit' => EditNumberSequence::route('/{record}/edit'),
        ];
    }
}
