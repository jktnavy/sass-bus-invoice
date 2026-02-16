<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Services\TenantContext;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Tenant')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('code')->required()->maxLength(30)->columnSpan(4),
                        TextInput::make('name')->required()->maxLength(200)->columnSpan(8),
                        TextInput::make('npwp')->maxLength(40)->columnSpan(4),
                        TextInput::make('email')->email()->maxLength(255)->columnSpan(4),
                        TextInput::make('phone')->maxLength(30)->columnSpan(4),
                        Textarea::make('address')->rows(3)->columnSpanFull(),
                    ]),
                Section::make('Branding Dokumen (Quotation & Invoice)')
                    ->description('Upload logo perusahaan, logo cap, dan tanda tangan untuk dipakai di PDF dokumen.')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        FileUpload::make('company_logo_path')
                            ->label('Logo Perusahaan')
                            ->disk('public')
                            ->directory(fn ($record): string => 'tenant-assets/'.($record?->id ?? app(TenantContext::class)->tenantId() ?? 'draft').'/logos')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->helperText('Format disarankan PNG transparan, rasio landscape.')
                            ->columnSpan(4),
                        FileUpload::make('stamp_logo_path')
                            ->label('Logo Cap Perusahaan')
                            ->disk('public')
                            ->directory(fn ($record): string => 'tenant-assets/'.($record?->id ?? app(TenantContext::class)->tenantId() ?? 'draft').'/stamp')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->helperText('File ini akan digabung dengan tanda tangan.')
                            ->columnSpan(4),
                        FileUpload::make('signature_path')
                            ->label('Tanda Tangan')
                            ->disk('public')
                            ->directory(fn ($record): string => 'tenant-assets/'.($record?->id ?? app(TenantContext::class)->tenantId() ?? 'draft').'/signature')
                            ->image()
                            ->imageEditor()
                            ->visibility('public')
                            ->helperText('Upload scan tanda tangan, latar transparan lebih baik.')
                            ->columnSpan(4),
                        TextInput::make('signatory_name')
                            ->label('Nama Penandatangan')
                            ->placeholder('Contoh: Budi Santoso')
                            ->columnSpan(6),
                        TextInput::make('signatory_position')
                            ->label('Jabatan Penandatangan')
                            ->placeholder('Contoh: Direktur Operasional')
                            ->columnSpan(6),
                        TextInput::make('company_website')
                            ->label('Website Perusahaan')
                            ->placeholder('https://www.perusahaan.com')
                            ->columnSpan(6),
                        TextInput::make('bank_name')
                            ->label('Bank')
                            ->placeholder('Contoh: BCA')
                            ->columnSpan(4),
                        TextInput::make('bank_account_holder')
                            ->label('Account Holder')
                            ->placeholder('Contoh: Suhendi')
                            ->columnSpan(4),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->placeholder('Contoh: 406 061 5352')
                            ->columnSpan(4),
                        Textarea::make('document_notes')
                            ->label('Catatan Bawah Dokumen')
                            ->placeholder('Contoh: Pembayaran ditransfer ke BCA 123456789 a.n PT Bus Pariwisata')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
