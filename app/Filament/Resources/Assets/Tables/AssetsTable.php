<?php

namespace App\Filament\Resources\Assets\Tables;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Asset;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('registered_on', 'desc')->paginated([10, 25, 50, 100])->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('code')->label('Código')->searchable()->sortable(),
                TextColumn::make('description')->label('Descripción')->limit(65)->wrap()->searchable(['description', 'brand', 'model', 'serial_number']),
                TextColumn::make('budgetItem.name')->label('Partida')->wrap()->sortable()->toggleable(),
                TextColumn::make('responsible.last_name')->label('Responsable')->placeholder('Sin asignar')
                    ->formatStateUsing(fn (Asset $record) => $record->responsible?->first_name.' '.$record->responsible?->last_name)->toggleable(),
                TextColumn::make('unit.name')->label('Unidad')->placeholder('Sin unidad')->wrap()->sortable()->toggleable(),
                TextColumn::make('office.name')->label('Oficina')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('physicalStatus.name')->label('Estado físico')->badge()->toggleable(),
                TextColumn::make('administrativeStatus.name')->label('Situación')->badge()->toggleable(),
                TextColumn::make('registered_on')->label('Fecha de alta')->date('d/m/Y')->sortable()->toggleable(),
                TextColumn::make('category.name')->label('Categoría')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('brand')->label('Marca')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('model')->label('Modelo')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('serial_number')->label('Serie')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('value')->label('Valor')->numeric(decimalPlaces: 2)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizational_unit_id')->label('Unidad')->relationship('unit', 'name')->searchable(),
                SelectFilter::make('office_id')->label('Oficina')->relationship('office', 'name')->searchable(),
                SelectFilter::make('current_person_id')->label('Responsable')->relationship('responsible', 'last_name')
                    ->searchable(['first_name', 'last_name', 'institutional_id'])
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name.' '.$record->last_name),
                SelectFilter::make('budget_item_id')->label('Partida')->relationship('budgetItem', 'name')->searchable(),
                SelectFilter::make('asset_category_id')->label('Categoría')->relationship('category', 'name')->searchable(),
                SelectFilter::make('physical_status_id')->label('Estado físico')->relationship('physicalStatus', 'name')->preload(),
                SelectFilter::make('administrative_status_id')->label('Situación')->relationship('administrativeStatus', 'name')->preload(),
                Filter::make('registered_on')->label('Fecha de alta')->schema([
                    DatePicker::make('from')->label('Desde'), DatePicker::make('until')->label('Hasta'),
                ])->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('registered_on', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('registered_on', '<=', $date))),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->recordUrl(fn (Asset $record) => AssetResource::getUrl('view', ['record' => $record]));
    }
}
