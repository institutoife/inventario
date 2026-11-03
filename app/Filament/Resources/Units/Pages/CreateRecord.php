<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Pages\FoundationCreateRecord;
use App\Filament\Resources\Units\OrganizationalUnitResource;

class CreateRecord extends FoundationCreateRecord
{
    protected static string $resource = OrganizationalUnitResource::class;
}
