<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\FoundationResource;
use App\Filament\Resources\Users\Pages\CreateRecord;
use App\Filament\Resources\Users\Pages\EditRecord;
use App\Filament\Resources\Users\Pages\ListRecords;
use App\Models\User;

class UserResource extends FoundationResource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    public static string $resourceKey = 'users';

    public static function getPages(): array
    {
        return ['index' => ListRecords::route('/'), 'create' => CreateRecord::route('/create'), 'edit' => EditRecord::route('/{record}/edit')];
    }
}
