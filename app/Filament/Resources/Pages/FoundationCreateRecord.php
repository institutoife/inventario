<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

abstract class FoundationCreateRecord extends CreateRecord
{
    use PersistsFoundation;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->persistFoundation($data);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
