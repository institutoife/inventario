<?php

namespace App\Filament\Resources\Locations;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Locations\Pages\CreateRecord;
use App\Filament\Resources\Locations\Pages\EditRecord;
use App\Filament\Resources\Locations\Pages\ListRecords;
use App\Models\Location;

class LocationResource extends FoundationResource
{
    protected static ?string $model = Location::class;

    protected static ?string $slug = 'locations';

    public static string $resourceKey = 'locations';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
