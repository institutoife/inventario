<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\AssetCategoryResource;
use App\Filament\Resources\Pages\FoundationListRecords;

class ListRecords extends FoundationListRecords
{
    protected static string $resource = AssetCategoryResource::class;
}
