<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Support\Pages\CreateRecordPage;

class CreateAuditLog extends CreateRecordPage
{
    protected static string $resource = AuditLogResource::class;
}
