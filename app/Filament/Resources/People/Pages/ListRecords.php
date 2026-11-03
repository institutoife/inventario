<?php

namespace App\Filament\Resources\People\Pages;

use App\Filament\Resources\Pages\FoundationListRecords;
use App\Filament\Resources\People\PersonResource;

class ListRecords extends FoundationListRecords
{
    protected static string $resource = PersonResource::class;
}
