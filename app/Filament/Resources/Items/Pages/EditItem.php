<?php

namespace App\Filament\Resources\Items\Pages;

use App\Filament\Resources\Items\ItemResource;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use App\Filament\Support\Pages\EditRecordPage;

class EditItem extends EditRecordPage
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
