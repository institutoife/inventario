<?php

namespace App\Filament\Resources\People;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\People\Pages\CreateRecord;
use App\Filament\Resources\People\Pages\EditRecord;
use App\Filament\Resources\People\Pages\ListRecords;
use App\Models\Person;

class PersonResource extends FoundationResource
{
    protected static ?string $model = Person::class;

    protected static ?string $slug = 'people';

    public static string $resourceKey = 'people';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
