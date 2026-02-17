<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Tenant;
use App\Services\TenantContext;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi User')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Tenant')
                            ->options(fn () => Tenant::query()->pluck('name', 'id')->all())
                            ->visible(fn () => auth()->user()?->role === 'superadmin')
                            ->required(fn () => auth()->user()?->role === 'superadmin')
                            ->default(fn () => app(TenantContext::class)->tenantId())
                            ->columnSpan(4),
                        TextInput::make('name')->required()->maxLength(150)->columnSpan(4),
                        TextInput::make('email')->email()->required()->maxLength(255)->columnSpan(4),
                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'sales' => 'Sales',
                                'finance' => 'Finance',
                            ])
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('password_hash')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpan(4),
                        Select::make('is_active')->options([1 => 'Active', 0 => 'Inactive'])->default(1)->required()->columnSpan(4),
                    ]),
            ]);
    }
}
