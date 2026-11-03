<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateRecord;
use App\Filament\Resources\Categories\Pages\EditRecord;
use App\Filament\Resources\Categories\Pages\ListRecords;
use App\Filament\Resources\FoundationResource;
use App\Models\AssetCategory;

class AssetCategoryResource extends FoundationResource
{
    protected static ?string $model = AssetCategory::class;

    protected static ?string $slug = 'categories';

    public static string $resourceKey = 'categories';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
