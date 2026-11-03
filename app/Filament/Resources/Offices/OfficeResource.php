<?php

namespace App\Filament\Resources\Offices;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Offices\Pages\CreateRecord;
use App\Filament\Resources\Offices\Pages\EditRecord;
use App\Filament\Resources\Offices\Pages\ListRecords;
use App\Models\Office;

class OfficeResource extends FoundationResource
{
    protected static ?string $model = Office::class;

    protected static ?string $slug = 'offices';

    public static string $resourceKey = 'offices';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
