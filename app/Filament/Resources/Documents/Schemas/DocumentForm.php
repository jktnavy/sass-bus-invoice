<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Documents')
                    ->columnSpanFull()
                    ->schema([]),
            ]);
    }
}
