<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Resources\Assets\Pages\Concerns\CallsAssetAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class EditAsset extends EditRecord
{
    use CallsAssetAction;

    protected static string $resource = AssetResource::class;

    #[Locked]
    public int $recordVersion = 0;

    #[Locked]
    public string $recordToken = '';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->recordVersion = (int) $data['version'];
        $this->recordToken = hash('sha256', json_encode($this->getRecord()->getAttributes()));
        $data['reason'] = null;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $saved = $this->persistAsset([...$data, 'version' => $this->recordVersion], $record, $this->recordToken);
        $record->setRawAttributes($saved->getAttributes(), true);
        $this->recordVersion = (int) $saved->version;
        $this->recordToken = hash('sha256', json_encode($saved->getAttributes()));

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    protected function getRedirectUrl(): string
    {
        return AssetResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
