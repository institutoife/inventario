<?php

namespace App\Filament\Resources\Ranks;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Ranks\Pages\CreateRecord;
use App\Filament\Resources\Ranks\Pages\EditRecord;
use App\Filament\Resources\Ranks\Pages\ListRecords;
use App\Models\Rank;

class RankResource extends FoundationResource
{
    protected static ?string $model = Rank::class;

    protected static ?string $slug = 'ranks';

    public static string $resourceKey = 'ranks';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
