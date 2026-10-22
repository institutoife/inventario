<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoundationRequest;
use App\Models\AdministrativeStatus;
use App\Models\Asset;
use App\Models\Person;
use App\Models\Role;
use App\Services\AdminResources as Resources;
use App\Services\Audit;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FoundationController extends Controller
{
    public function index(string $resource)
    {
        [$model,$title,$permission,$fields] = Resources::get($resource);
        Gate::authorize($permission);
        $rows = $model::orderByDesc('id')->paginate(25);

        return view('admin.index', compact('resource', 'title', 'fields', 'rows'));
    }

    public function form(string $resource, ?int $id = null)
    {
        [$model,$title,$permission,$fields] = Resources::get($resource);
        Gate::authorize($resource === 'assets' ? ($id ? 'assets.update' : 'assets.create') : $permission);
        $row = $id ? $model::findOrFail($id) : new $model;
        $options = [];
        foreach ($fields as $field => $label) {
            if ($ref = Resources::reference($resource, $field)) {
                $options[$field] = $ref::where('active', true)->get();
            }
        }
        if ($resource === 'users') {
            $options['roles'] = Role::all();
        }

        return view('admin.form', compact('resource', 'title', 'fields', 'row', 'options'));
    }

    public function save(FoundationRequest $request, string $resource, ?int $id = null)
    {
        [$model] = Resources::get($resource);
        $data = $request->validated();
        DB::transaction(function () use ($data, $model, $resource, $id) {
            $row = $id ? $model::lockForUpdate()->findOrFail($id) : new $model;
            if ($id && ! hash_equals(hash('sha256', json_encode($row->getAttributes())), (string) request('record_token'))) {
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
            if ($resource === 'assets') {
                if ($id) {
                    abort_unless((int) $row->version === (int) ($data['version'] ?? 0), 409);
                } else {
                    $values['administrative_status_id'] = AdministrativeStatus::where('code', 'available')->sole()->id;
                }
                $values['version'] = $id ? $row->version + 1 : 1;
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
        });

        return redirect()->route('admin.index', $resource)->with('status', 'Registro guardado.');
    }
}
