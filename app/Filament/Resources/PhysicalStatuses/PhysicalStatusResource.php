<?php

namespace App\Filament\Resources\PhysicalStatuses;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\PhysicalStatuses\Pages\CreateRecord;
use App\Filament\Resources\PhysicalStatuses\Pages\EditRecord;
use App\Filament\Resources\PhysicalStatuses\Pages\ListRecords;
use App\Models\PhysicalStatus;

class PhysicalStatusResource extends FoundationResource
{
    protected static ?string $model = PhysicalStatus::class;

    protected static ?string $slug = 'physical-statuses';

    public static string $resourceKey = 'physical-statuses';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
