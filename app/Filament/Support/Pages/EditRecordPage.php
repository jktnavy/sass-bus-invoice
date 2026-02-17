<?php

namespace App\Filament\Support\Pages;

use App\Support\FriendlyExceptionMessage;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class EditRecordPage extends EditRecord
{
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'data' => FriendlyExceptionMessage::from($exception),
            ]);
        }
    }
}
