<?php

namespace App\Filament\Support\Pages;

use App\Support\FriendlyExceptionMessage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class CreateRecordPage extends CreateRecord
{
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'data' => FriendlyExceptionMessage::from($exception),
            ]);
        }
    }
}
