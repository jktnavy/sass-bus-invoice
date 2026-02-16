<?php

namespace App\Filament\Resources\Tenants\Pages;

use App\Filament\Resources\Tenants\TenantResource;
use App\Models\User;
use App\Services\TenantBrandingService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $settings = $data['settings'] ?? [];

        $data['company_logo_path'] = $settings['company_logo_path'] ?? null;
        $data['stamp_logo_path'] = $settings['stamp_logo_path'] ?? null;
        $data['signature_path'] = $settings['signature_path'] ?? null;
        $data['signatory_name'] = $settings['signatory_name'] ?? null;
        $data['signatory_position'] = $settings['signatory_position'] ?? null;
        $data['company_website'] = $settings['company_website'] ?? null;
        $data['bank_name'] = $settings['bank_name'] ?? null;
        $data['bank_account_holder'] = $settings['bank_account_holder'] ?? null;
        $data['bank_account_number'] = $settings['bank_account_number'] ?? null;
        $data['document_notes'] = $settings['document_notes'] ?? null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $settings = $this->record->settings ?? [];

        $settings['company_logo_path'] = $data['company_logo_path'] ?? null;
        $settings['stamp_logo_path'] = $data['stamp_logo_path'] ?? null;
        $settings['signature_path'] = $data['signature_path'] ?? null;
        $settings['signatory_name'] = $data['signatory_name'] ?? null;
        $settings['signatory_position'] = $data['signatory_position'] ?? null;
        $settings['company_website'] = $data['company_website'] ?? null;
        $settings['bank_name'] = $data['bank_name'] ?? null;
        $settings['bank_account_holder'] = $data['bank_account_holder'] ?? null;
        $settings['bank_account_number'] = $data['bank_account_number'] ?? null;
        $settings['document_notes'] = $data['document_notes'] ?? null;

        $data['settings'] = $settings;

        unset(
            $data['company_logo_path'],
            $data['stamp_logo_path'],
            $data['signature_path'],
            $data['signatory_name'],
            $data['signatory_position'],
            $data['company_website'],
            $data['bank_name'],
            $data['bank_account_holder'],
            $data['bank_account_number'],
            $data['document_notes'],
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalDescription('Tenant hanya bisa dihapus jika semua user tenant tersebut sudah dipindahkan atau dihapus terlebih dahulu.')
                ->before(function (): void {
                    $userCount = User::query()
                        ->withoutGlobalScopes()
                        ->where('tenant_id', $this->record->id)
                        ->count();

                    if ($userCount === 0) {
                        return;
                    }

                    Notification::make()
                        ->title('Tenant tidak bisa dihapus')
                        ->body("Masih ada {$userCount} user yang terhubung ke tenant ini.")
                        ->danger()
                        ->send();

                    throw new Halt();
                })
                ->visible(fn (): bool => auth()->user()?->role === 'superadmin'),
        ];
    }

    protected function afterSave(): void
    {
        app(TenantBrandingService::class)->refreshMergedStampSignature($this->record);
    }
}
