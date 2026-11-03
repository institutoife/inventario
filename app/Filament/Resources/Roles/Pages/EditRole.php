<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use App\Services\Audit;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    #[Locked]
    public array $originalPermissions = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalPermissions = $this->getRecord()->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all();

        return [...$data, 'permissions' => $this->originalPermissions, 'reason' => null];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Gate::authorize('users.manage');
        abort_if($record->code === 'superadmin', 403);
        $data = Validator::make($data, ['permissions' => 'nullable|array', 'permissions.*' => 'integer|distinct|exists:permissions,id', 'reason' => 'required|string|max:2000'])->validate();

        return DB::transaction(function () use ($record, $data) {
            $role = Role::lockForUpdate()->findOrFail($record->id);
            $old = $role->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all();
            if ($old !== $this->originalPermissions) {
                throw ValidationException::withMessages(['data.reason' => 'Los permisos cambiaron. Recargue la página.']);
            }
            $role->permissions()->sync($data['permissions'] ?? []);
            Audit::record('permissions.update', 'roles', $role->id, ['permissions' => $old], ['permissions' => $data['permissions'] ?? []], $data['reason']);
            $this->originalPermissions = $role->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all();

            return $role;
        });
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
