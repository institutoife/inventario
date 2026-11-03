<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Resources\Pages\FoundationEditRecord;
use App\Filament\Resources\People\PersonResource;

class EditRecord extends FoundationEditRecord
{
    protected static string $resource = PersonResource::class;
}
