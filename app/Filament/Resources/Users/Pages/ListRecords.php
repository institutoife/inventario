<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\FoundationListRecords;
use App\Filament\Resources\Users\UserResource;

class ListRecords extends FoundationListRecords
{
    protected static string $resource = UserResource::class;
}
