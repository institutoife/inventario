<?php

namespace App\Filament\Resources\AssetOrigins;

use App\Filament\Resources\FoundationResource;
use App\Models\AssetOrigin;

class AssetOriginResource extends FoundationResource
{
    protected static ?string $model = AssetOrigin::class;

    protected static ?string $slug = 'asset-origins';

    public static string $resourceKey = 'asset-origins';

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
