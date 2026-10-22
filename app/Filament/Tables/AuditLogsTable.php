<?php

namespace App\Filament\Tables;

use App\Models\AuditLog;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->paginated([10, 25, 50])->columns([
            TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s')->sortable(),
            TextColumn::make('user.name')->label('Usuario')->placeholder('Sistema'),
            TextColumn::make('action')->label('Acción')->badge()->formatStateUsing(fn (string $state) => match ($state) {
                'create' => 'Creación', 'update' => 'Actualización', 'login' => 'Ingreso', 'logout' => 'Salida', 'bootstrap' => 'Alta administrativa', default => $state,
            }),
            TextColumn::make('entity_type')->label('Entidad')->searchable(),
            TextColumn::make('entity_id')->label('Registro'),
            TextColumn::make('reason')->label('Motivo')->limit(70)->wrap(),
            TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            SelectFilter::make('action')->label('Acción')->options(['create' => 'Creación', 'update' => 'Actualización', 'login' => 'Ingreso', 'logout' => 'Salida', 'bootstrap' => 'Alta administrativa']),
        ])->recordActions([
            Action::make('details')->label('Ver detalle')->icon('heroicon-o-eye')->authorize('view')
                ->modalHeading('Detalle de auditoría')->modalSubmitAction(false)->modalCancelActionLabel('Cerrar')->schema([
                    TextEntry::make('reason')->label('Motivo'),
                    TextEntry::make('old_values')->label('Antes')->state(fn (AuditLog $record) => json_encode($record->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    TextEntry::make('new_values')->label('Después')->state(fn (AuditLog $record) => json_encode($record->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                ]),
        ]);
    }
}
