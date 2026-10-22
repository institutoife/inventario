<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\BudgetItem;
use App\Models\Location;
use App\Models\Office;
use App\Models\OrganizationalUnit;
use App\Models\PhysicalStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Identificación')->columns(2)->columnSpanFull()->schema([
                TextInput::make('code')->label('Código')->required()->maxLength(100)->unique(ignoreRecord: true),
                DatePicker::make('registered_on')->label('Fecha de alta')->required()->maxDate(today())->format('Y-m-d'),
                Textarea::make('description')->label('Descripción')->required()->maxLength(10000)->rows(3)->columnSpanFull(),
                AssetSelect::make('asset_category_id', 'Categoría', AssetCategory::class),
                AssetSelect::make('budget_item_id', 'Partida', BudgetItem::class),
            ]),
            Section::make('Características')->columns(3)->columnSpanFull()->schema([
                TextInput::make('brand')->label('Marca')->maxLength(255),
                TextInput::make('model')->label('Modelo')->maxLength(255),
                TextInput::make('serial_number')->label('Número de serie')->maxLength(255),
            ]),
            Section::make('Ubicación')->columns(3)->columnSpanFull()->schema([
                AssetSelect::make('organizational_unit_id', 'Unidad', OrganizationalUnit::class)->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('office_id', null);
                        $set('location_id', null);
                    }),
                AssetSelect::make('office_id', 'Oficina', Office::class, fn (Builder $query, Get $get) => $query->where('organizational_unit_id', $get('organizational_unit_id')))
                    ->live()->afterStateUpdated(fn (Set $set) => $set('location_id', null)),
                AssetSelect::make('location_id', 'Ubicación', Location::class, fn (Builder $query, Get $get) => $query->where('office_id', $get('office_id'))->where('is_deposit', false)),
            ]),
            Section::make('Responsabilidad y estado')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('current_responsible')->label('Responsable actual')
                    ->state(fn (?Asset $record) => $record?->responsible ? $record->responsible->first_name.' '.$record->responsible->last_name : 'Sin asignar'),
                TextEntry::make('current_status')->label('Situación administrativa')
                    ->state(fn (?Asset $record) => $record?->administrativeStatus?->name ?? 'Disponible'),
                AssetSelect::make('physical_status_id', 'Estado físico', PhysicalStatus::class),
            ]),
            Section::make('Información administrativa')->columns(3)->columnSpanFull()->schema([
                TextInput::make('origin')->label('Procedencia')->maxLength(255),
                TextInput::make('incorporation_method')->label('Forma de incorporación')->maxLength(255),
                TextInput::make('value')->label('Valor registrado')->numeric()->minValue(0)->step('0.01')->rule('decimal:0,2'),
            ]),
            Section::make('Observaciones y justificación')->columnSpanFull()->schema([
                Textarea::make('observations')->label('Observaciones')->rows(3)->maxLength(10000),
                Textarea::make('reason')->label('Motivo del registro o cambio')->required()->maxLength(2000)->rows(2)
                    ->helperText('El motivo quedará registrado junto con el cambio.'),
            ]),
        ]);
    }
}
