<?php

namespace App\Filament\Resources\Roles;

use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): Response
    {
        $allowed = static::canViewAny() && (in_array($action, ['viewAny', 'view']) || ($action === 'update' && $record && $record->code !== 'superadmin'));

        return $allowed ? Response::allow() : Response::deny();
    }

    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }

    public static function getModelLabel(): string
    {
        return 'Rol';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Roles y permisos';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('users.manage') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny() && $record->code !== 'superadmin';
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->disabled(),
            Select::make('permissions')->label('Permisos')->multiple()->searchable()->options(fn () => Permission::pluck('name', 'id')->all()),
            Textarea::make('reason')->label('Motivo')->required()->maxLength(2000),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Rol'), TextColumn::make('permissions.name')->label('Permisos')->listWithLineBreaks()->limitList(3)->expandableLimitedList()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRoles::route('/'), 'edit' => Pages\EditRole::route('/{record}/edit')];
    }
}
