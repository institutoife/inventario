<?php

namespace App\Domain\Assets;

use App\Models\Asset;
use App\Models\Location;
use App\Models\Office;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class AssetData
{
    public const FIELDS = [
        'code', 'description', 'registered_on', 'budget_item_id', 'asset_category_id',
        'physical_status_id', 'organizational_unit_id', 'office_id', 'location_id',
        'origin', 'incorporation_method', 'value', 'brand', 'model', 'serial_number', 'observations',
    ];

    public static function rules(?Asset $asset = null): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:100', Rule::unique('assets', 'code')->ignore($asset?->id)],
            'description' => ['required', 'string', 'max:10000'],
            'registered_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999.99', 'decimal:0,2'],
            'observations' => ['nullable', 'string', 'max:10000'],
            'reason' => ['required', 'string', 'max:2000', 'regex:/\S/u'],
            'version' => [$asset ? 'required' : 'nullable', 'integer', 'min:1'],
            'current_person_id' => ['prohibited'],
            'administrative_status_id' => ['prohibited'],
            'active' => ['prohibited'],
        ];
        foreach (['origin', 'incorporation_method', 'brand', 'model', 'serial_number'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        foreach ([
            'budget_item_id' => 'budget_items', 'asset_category_id' => 'asset_categories',
            'physical_status_id' => 'physical_statuses', 'organizational_unit_id' => 'organizational_units',
            'office_id' => 'offices', 'location_id' => 'locations',
        ] as $field => $table) {
            $rules[$field] = ['nullable', 'integer', Rule::exists($table, 'id')->where('active', true)];
        }

        return $rules;
    }

    public static function validate(array $input, ?Asset $asset = null): array
    {
        $validator = Validator::make($input, self::rules($asset), [
            'reason.regex' => 'Indique un motivo válido para registrar el cambio.',
            '*.prohibited' => 'Este dato requiere una operación institucional específica.',
        ]);
        $validator->after(fn (ValidationValidator $validator) => self::validateLocation($validator, $input));

        return $validator->validate();
    }

    public static function validateLocation(ValidationValidator $validator, array $input): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }
        if (filled($input['office_id'] ?? null)) {
            $office = Office::find($input['office_id']);
            if (! $office || (int) $office->organizational_unit_id !== (int) ($input['organizational_unit_id'] ?? null)) {
                $validator->errors()->add('office_id', 'La oficina debe pertenecer a la unidad seleccionada.');
            }
        }
        if (filled($input['location_id'] ?? null)) {
            $location = Location::find($input['location_id']);
            if (! $location || (int) $location->office_id !== (int) ($input['office_id'] ?? null) || $location->is_deposit) {
                $validator->errors()->add('location_id', 'Seleccione una ubicación de la oficina que no sea depósito.');
            }
        }
    }
}
