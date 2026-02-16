<?php

namespace App\Filament\Actions;

use App\Support\FriendlyExceptionMessage;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SafeDeleteAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->using(function (Model $record): bool {
            try {
                return (bool) $record->delete();
            } catch (Throwable $exception) {
                Notification::make()
                    ->title('Hapus data gagal')
                    ->body(FriendlyExceptionMessage::from($exception))
                    ->danger()
                    ->send();

                return false;
            }
        });
    }
}

