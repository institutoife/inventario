<?php

namespace App\Filament\Resources\Positions;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Positions\Pages\CreateRecord;
use App\Filament\Resources\Positions\Pages\EditRecord;
use App\Filament\Resources\Positions\Pages\ListRecords;
use App\Models\Position;

class PositionResource extends FoundationResource
{
    protected static ?string $model = Position::class;

    protected static ?string $slug = 'positions';

    public static string $resourceKey = 'positions';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
