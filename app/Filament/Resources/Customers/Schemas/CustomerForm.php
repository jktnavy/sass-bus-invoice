<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Services\TenantContext;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Customer')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->maxLength(40)
                            ->unique(
                                table: 'customers',
                                column: 'code',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('tenant_id', app(TenantContext::class)->tenantId())
                            )
                            ->validationMessages([
                                'unique' => 'Kode customer sudah digunakan pada tenant ini.',
                            ])
                            ->columnSpan(4),
                        TextInput::make('name')->required()->maxLength(200)->columnSpan(8),
                        TextInput::make('npwp')->maxLength(40)->columnSpan(4),
                        TextInput::make('payment_terms_days')->numeric()->default(0)->columnSpan(4),
                        Textarea::make('billing_address')->columnSpanFull(),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}
