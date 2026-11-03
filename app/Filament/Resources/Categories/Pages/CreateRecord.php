<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\AssetCategoryResource;
use App\Filament\Resources\Pages\FoundationCreateRecord;

class CreateRecord extends FoundationCreateRecord
{
    protected static string $resource = AssetCategoryResource::class;
}
