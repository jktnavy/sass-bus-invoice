<?php

namespace App\Filament\Pages;

use App\Support\RoleHelper;
use BackedEnum;
use Filament\Pages\Page;

class Panduan extends Page
{
    protected string $view = 'filament.pages.panduan';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Help';

    protected static ?string $title = 'Panduan Penggunaan Sistem';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return RoleHelper::hasAnyRole(auth()->user(), ['admin', 'sales', 'finance']);
    }
}
