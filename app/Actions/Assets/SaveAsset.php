<?php

namespace App\Actions\Assets;

use App\Domain\Assets\AssetData;
use App\Models\AdministrativeStatus;
use App\Models\Asset;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveAsset
{
    public function execute(User $actor, array $input, ?Asset $asset = null, ?string $expectedToken = null): Asset
    {
        Gate::forUser($actor)->authorize($asset ? 'update' : 'create', $asset ?? Asset::class);

        return DB::transaction(function () use ($actor, $input, $asset, $expectedToken): Asset {
            $row = $asset ? Asset::lockForUpdate()->findOrFail($asset->id) : new Asset;
            if ($asset) {
                Gate::forUser($actor)->authorize('update', $row);
                abort_unless((int) ($input['version'] ?? 0) === (int) $row->version, 409, 'El activo cambió; vuelva a cargarlo.');
                if ($expectedToken !== null) {
                    abort_unless(hash_equals(hash('sha256', json_encode($row->getAttributes())), $expectedToken), 409, 'El activo cambió; vuelva a cargarlo.');
                }
            }
            $data = AssetData::validate($input, $asset ? $row : null);
            $values = Arr::only($data, AssetData::FIELDS);
            if ($asset && ($row->current_person_id || $row->administrativeStatus?->code !== 'available')) {
                foreach (['organizational_unit_id', 'office_id', 'location_id'] as $field) {
                    if (array_key_exists($field, $values) && (int) $values[$field] !== (int) $row->{$field}) {
                        throw ValidationException::withMessages([$field => 'El traslado de este activo requiere un movimiento institucional.']);
                    }
                }
            }
            $before = $row->exists ? $row->toArray() : [];
            if (! $asset) {
                $values['administrative_status_id'] = AdministrativeStatus::where('code', 'available')->where('active', true)->sole()->id;
            }
            $values['version'] = $asset ? $row->version + 1 : 1;
            $row->fill($values)->save();
            $row->refresh();
            Audit::record($asset ? 'update' : 'create', 'assets', $row->id, $before, $row->toArray(), $data['reason'], actorId: $actor->id);

            return $row;
        });
    }
}
