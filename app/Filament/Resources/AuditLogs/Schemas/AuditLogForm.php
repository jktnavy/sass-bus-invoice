<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Audit Logs')
                    ->columnSpanFull()
                    ->schema([]),
            ]);
    }
}
