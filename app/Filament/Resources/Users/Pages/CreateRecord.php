<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\FoundationCreateRecord;
use App\Filament\Resources\Users\UserResource;

class CreateRecord extends FoundationCreateRecord
{
    protected static string $resource = UserResource::class;
}
