<?php

namespace App\Filament\Resources\NumberSequences\Pages;

use App\Filament\Resources\NumberSequences\NumberSequenceResource;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use App\Filament\Support\Pages\EditRecordPage;

class EditNumberSequence extends EditRecordPage
{
    protected static string $resource = NumberSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
