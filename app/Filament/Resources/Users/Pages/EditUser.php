<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Actions\SafeDeleteAction as DeleteAction;
use App\Filament\Support\Pages\EditRecordPage;

class EditUser extends EditRecordPage
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
