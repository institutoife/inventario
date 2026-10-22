<?php

namespace App\Http\Requests;

use App\Domain\Assets\AssetData;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Office;
use App\Services\AdminResources as Resources;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FoundationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $key = $this->route('resource');
        $config = Resources::get($key);
        $permission = $key === 'assets' ? ($this->route('id') ? 'assets.update' : 'assets.create') : $config[2];

        return $this->user()->can($permission);
    }

    public function rules(): array
    {
        $key = $this->route('resource');
        if ($key === 'assets') {
            return AssetData::rules($this->route('id') ? Asset::findOrFail($this->route('id')) : null);
        }
        [$model,,,$fields] = Resources::get($key);
        $table = (new $model)->getTable();
        $rules = ['reason' => 'required|string|max:2000', 'version' => 'nullable|integer|min:1'];
        foreach ($fields as $field => $label) {
            if ($ref = Resources::reference($key, $field)) {
                $required = ($key === 'offices' && $field === 'organizational_unit_id') || ($key === 'locations' && $field === 'office_id');
                $rules[$field] = [$required ? 'required' : 'nullable', 'integer', Rule::exists((new $ref)->getTable(), 'id')->where('active', true)];
            } elseif (in_array($field, ['active', 'is_deposit'])) {
                $rules[$field] = 'required|boolean';
            } elseif ($field === 'roles') {
                $rules[$field] = 'required|array|min:1';
                $rules['roles.*'] = 'required|integer|distinct|exists:roles,id';
            } elseif ($field === 'password') {
                $rules[$field] = [$this->route('id') ? 'nullable' : 'required', 'string', 'min:12', 'max:255'];
            } elseif ($field === 'email') {
                $rules[$field] = ['required', 'email', 'max:255', Rule::unique($table, $field)->ignore($this->route('id'))];
            } elseif ($field === 'code') {
                $rules[$field] = ['required', 'string', 'max:100', Rule::unique($table, $field)->ignore($this->route('id'))];
            } elseif ($field === 'institutional_id') {
                $rules[$field] = ['nullable', 'string', 'max:255', Rule::unique($table, $field)->ignore($this->route('id'))];
            } elseif ($field === 'registered_on') {
                $rules[$field] = 'required|date_format:Y-m-d|before_or_equal:today';
            } elseif ($field === 'value') {
                $rules[$field] = 'nullable|numeric|min:0|max:9999999999999999.99|decimal:0,2';
            } else {
                $rules[$field] = (in_array($field, ['name', 'first_name', 'last_name', 'description']) ? 'required' : 'nullable').'|string|max:'.(in_array($field, ['description', 'observations']) ? '10000' : '255');
            }
        }

        return $rules;
    }

    public function after(): array
    {
        return [function ($validator) {
            $key = $this->route('resource');
            if ($key === 'assets') {
                AssetData::validateLocation($validator, $this->all());

                return;
            }
            if ($this->filled('office_id')) {
                $office = Office::find($this->office_id);
                if (! $office || (int) $office->organizational_unit_id !== (int) $this->organizational_unit_id) {
                    $validator->errors()->add('office_id', 'La oficina debe pertenecer a la unidad seleccionada.');
                }
            }
            if ($this->filled('location_id')) {
                $location = Location::find($this->location_id);
                if (! $location || (int) $location->office_id !== (int) $this->office_id || $location->is_deposit) {
                    $validator->errors()->add('location_id', 'Seleccione una ubicación de la oficina que no sea depósito.');
                }
            }
            if ($this->filled('parent_id')) {
                $model = Resources::get($key)[0];
                $parent = $model::find($this->parent_id);
                $seen = [];
                while ($parent) {
                    if ($parent->id == (int) $this->route('id') || isset($seen[$parent->id])) {
                        $validator->errors()->add('parent_id', 'La jerarquía no admite ciclos.');
                        break;
                    }
                    $seen[$parent->id] = true;
                    $parent = $model::find($parent->parent_id);
                }
            }
        }];
    }
}
