<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Support\Pages\CreateRecordPage;

class CreateCustomer extends CreateRecordPage
{
    protected static string $resource = CustomerResource::class;
}
