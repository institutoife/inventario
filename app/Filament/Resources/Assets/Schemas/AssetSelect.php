<?php

namespace App\Filament\Resources\Assets\Schemas;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;

class AssetSelect
{
    public static function make(string $field, string $label, string $model, ?Closure $scope = null): Select
    {
        return Select::make($field)->label($label)->searchable()->optionsLimit(50)
            ->getSearchResultsUsing(function (string $search, Get $get) use ($model, $scope): array {
                $query = $model::query()->where('active', true);
                if ($scope) {
                    $scope($query, $get);
                }

                return $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%'))
                    ->orderBy('name')->limit(50)->get()->mapWithKeys(fn ($record) => [$record->id => $record->code.' — '.$record->name])->all();
            })
            ->getOptionLabelUsing(fn ($value) => ($record = $model::find($value)) ? $record->code.' — '.$record->name : null);
    }
}
