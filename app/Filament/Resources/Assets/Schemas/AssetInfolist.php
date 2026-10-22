<?php

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\Asset;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Datos principales')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('code')->label('Código'),
                TextEntry::make('registered_on')->label('Fecha de alta')->date('d/m/Y'),
                TextEntry::make('description')->label('Descripción')->columnSpanFull(),
                TextEntry::make('category.name')->label('Categoría')->placeholder('Sin categoría'),
                TextEntry::make('budgetItem.name')->label('Partida')->placeholder('Sin partida'),
            ]),
            Section::make('Ubicación actual')->schema([
                TextEntry::make('unit.name')->label('Unidad')->placeholder('Sin unidad'),
                TextEntry::make('office.name')->label('Oficina')->placeholder('Sin oficina'),
                TextEntry::make('location.name')->label('Ubicación')->placeholder('Sin ubicación'),
            ]),
            Section::make('Responsable y estado')->schema([
                TextEntry::make('responsible_name')->label('Responsable actual')
                    ->state(fn (Asset $record) => $record->responsible ? $record->responsible->first_name.' '.$record->responsible->last_name : 'Sin asignar'),
                TextEntry::make('physicalStatus.name')->label('Estado físico')->placeholder('Sin clasificar'),
                TextEntry::make('administrativeStatus.name')->label('Situación')->badge()->placeholder('Sin clasificar'),
            ]),
            Section::make('Datos técnicos')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('brand')->label('Marca')->placeholder('Sin especificar'),
                TextEntry::make('model')->label('Modelo')->placeholder('Sin especificar'),
                TextEntry::make('serial_number')->label('Serie')->placeholder('Sin especificar'),
            ]),
            Section::make('Origen e incorporación')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('origin')->label('Procedencia')->placeholder('Sin especificar'),
                TextEntry::make('incorporation_method')->label('Forma de incorporación')->placeholder('Sin especificar'),
                TextEntry::make('value')->label('Valor registrado')->numeric(decimalPlaces: 2)->placeholder('Sin valor'),
            ]),
            Section::make('Observaciones')->columnSpanFull()->schema([
                TextEntry::make('observations')->hiddenLabel()->placeholder('Sin observaciones'),
            ]),
        ]);
    }
}
