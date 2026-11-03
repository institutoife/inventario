<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Resources\Pages\FoundationCreateRecord;
use App\Filament\Resources\People\PersonResource;

class CreateRecord extends FoundationCreateRecord
{
    protected static string $resource = PersonResource::class;
}
