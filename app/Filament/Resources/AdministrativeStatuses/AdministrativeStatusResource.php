<?php

namespace App\Filament\Resources\AdministrativeStatuses;

use App\Filament\Resources\AdministrativeStatuses\Pages\CreateRecord;
use App\Filament\Resources\AdministrativeStatuses\Pages\EditRecord;
use App\Filament\Resources\AdministrativeStatuses\Pages\ListRecords;
use App\Filament\Resources\FoundationResource;
use App\Models\AdministrativeStatus;

class AdministrativeStatusResource extends FoundationResource
{
    protected static ?string $model = AdministrativeStatus::class;

    protected static ?string $slug = 'administrative-statuses';

    public static string $resourceKey = 'administrative-statuses';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
