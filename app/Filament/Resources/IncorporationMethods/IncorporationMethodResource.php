<?php

namespace App\Filament\Resources\IncorporationMethods;

use App\Filament\Resources\FoundationResource;
use App\Models\IncorporationMethod;

class IncorporationMethodResource extends FoundationResource
{
    protected static ?string $model = IncorporationMethod::class;

    protected static ?string $slug = 'incorporation-methods';

    public static string $resourceKey = 'incorporation-methods';

    public static function getPages(): array
    {
        return ['index' => Pages\ListRecords::route('/'), 'create' => Pages\CreateRecord::route('/create'), 'edit' => Pages\EditRecord::route('/{record}/edit')];
    }
}
