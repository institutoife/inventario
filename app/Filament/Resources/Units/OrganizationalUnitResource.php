<?php

namespace App\Filament\Resources\Units;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Units\Pages\CreateRecord;
use App\Filament\Resources\Units\Pages\EditRecord;
use App\Filament\Resources\Units\Pages\ListRecords;
use App\Models\OrganizationalUnit;

class OrganizationalUnitResource extends FoundationResource
{
    protected static ?string $model = OrganizationalUnit::class;

    protected static ?string $slug = 'units';

    public static string $resourceKey = 'units';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
