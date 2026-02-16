<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use App\Filament\Support\Pages\EditRecordPage;

class EditCustomer extends EditRecordPage
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
