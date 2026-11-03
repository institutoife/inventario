<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Pages\FoundationEditRecord;
use App\Filament\Resources\Users\UserResource;

class EditRecord extends FoundationEditRecord
{
    protected static string $resource = UserResource::class;
}
