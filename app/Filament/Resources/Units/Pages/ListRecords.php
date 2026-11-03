<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Pages\FoundationListRecords;
use App\Filament\Resources\Units\OrganizationalUnitResource;

class ListRecords extends FoundationListRecords
{
    protected static string $resource = OrganizationalUnitResource::class;
}
