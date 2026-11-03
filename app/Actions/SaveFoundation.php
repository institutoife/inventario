<?php

namespace App\Actions;

use App\Http\Requests\FoundationRequest;
use App\Models\Asset;
use App\Models\Person;
use App\Models\Role;
use App\Services\AdminResources as Resources;
use App\Services\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class SaveFoundation
{
    public function execute(string $resource, array $input, ?Model $record = null, ?string $token = null): Model
    {
        abort_if($resource === 'assets', 404); // Assets must use their dedicated SaveAsset action.
        [$model,,$permission] = Resources::get($resource);
        Gate::authorize($permission);
        $id = $record?->getKey();
        $request = FoundationRequest::create('/', 'POST', $input);
        $route = new Route('POST', '/', fn () => null);
        $route->bind(Request::create('/'));
        $route->setParameter('resource', $resource);
        $route->setParameter('id', $id);
        $request->setRouteResolver(fn () => $route);
        $validator = Validator::make($input, $request->rules());
        foreach ($request->after() as $callback) {
            $validator->after($callback);
        }
        $data = $validator->validate();

        return DB::transaction(function () use ($data, $model, $resource, $id, $token) {
            $row = $id ? $model::lockForUpdate()->findOrFail($id) : new $model;
            if ($id && ! hash_equals(hash('sha256', json_encode($row->getAttributes())), (string) $token)) {
                abort(409, 'El registro cambió; vuelva a cargarlo.');
            }
            $old = $row->toArray();
            $values = Arr::except($data, ['reason', 'roles', 'version']);
            if ($id && $resource === 'offices' && (int) $row->organizational_unit_id !== (int) $values['organizational_unit_id']) {
                abort_if(Person::where('office_id', $id)->exists() || Asset::where('office_id', $id)->exists(), 422, 'La oficina tiene responsables o activos. Su traslado requiere un proceso institucional.');
            }
            if ($id && $resource === 'locations' && ((int) $row->office_id !== (int) $values['office_id'] || (bool) $row->is_deposit !== (bool) $values['is_deposit'])) {
                abort_if(Asset::where('location_id', $id)->exists(), 422, 'La ubicación tiene activos. El cambio requiere un movimiento.');
            }
            if ($resource === 'users' && empty($values['password'])) {
                unset($values['password']);
            }
            if ($resource === 'users' && $id === auth()->id()) {
                abort_unless($values['active'] && in_array(Role::where('code', 'superadmin')->value('id'), $data['roles']), 422, 'No puede desactivar o quitar su propio acceso de superadministración.');
            }
            $row->fill($values)->save();
            if ($resource === 'users') {
                $old['roles'] = $id ? $row->roles()->pluck('roles.id')->all() : [];
                $row->roles()->sync($data['roles']);
            }
            $new = $row->fresh()->toArray();
            if ($resource === 'users') {
                $new['roles'] = $row->roles()->pluck('roles.id')->all();
            }
            Audit::record($id ? 'update' : 'create', $row->getTable(), $row->id, $old, $new, $data['reason']);

            return $row->fresh();
        });
    }
}
