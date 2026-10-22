<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Assets\Pages\Concerns\CallsAssetAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAsset extends CreateRecord
{
    use CallsAssetAction;

    protected static string $resource = AssetResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->persistAsset($data);
    }

    protected function getRedirectUrl(): string
    {
        return AssetResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
