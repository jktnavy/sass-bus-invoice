<?php

namespace App\Filament\Resources\Taxes\Pages;

use App\Filament\Resources\Taxes\TaxResource;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use App\Filament\Support\Pages\EditRecordPage;

class EditTax extends EditRecordPage
{
    protected static string $resource = TaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
