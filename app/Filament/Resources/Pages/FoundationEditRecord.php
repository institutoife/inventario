<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

abstract class FoundationEditRecord extends EditRecord
{
    use PersistsFoundation;

    #[Locked]
    public string $recordToken = '';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->recordToken = hash('sha256', json_encode($this->getRecord()->getAttributes()));
        $data['reason'] = null;
        if (static::getResource()::$resourceKey === 'users') {
            $data['password'] = null;
            $data['roles'] = $this->getRecord()->roles()->pluck('roles.id')->all();
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $saved = $this->persistFoundation($data, $record, $this->recordToken);
        $this->recordToken = hash('sha256', json_encode($saved->getAttributes()));

        return $saved;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
