<?php

namespace App\Filament\Resources;

use App\Models\Role;
use App\Services\AdminResources;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

abstract class FoundationResource extends Resource
{
    public static function getAuthorizationResponse(string|\UnitEnum $action, ?Model $record = null): Response
    {
        $allowed = in_array($action, ['viewAny', 'view', 'create', 'update']) && static::canViewAny();

        return $allowed ? Response::allow() : Response::deny();
    }

    public static string $resourceKey;

    public static function getNavigationGroup(): ?string
    {
        return match (static::$resourceKey) {
            'units','offices','locations' => 'Organización', 'people' => 'Inventario',
            'users' => 'Administración', default => 'Catálogos',
        };
    }

    public static function getModelLabel(): string
    {
        return AdminResources::get(static::$resourceKey)[1];
    }

    public static function getPluralModelLabel(): string
    {
        return static::getModelLabel();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(AdminResources::get(static::$resourceKey)[2]) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
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
        $components = [];
        foreach (AdminResources::get(static::$resourceKey)[3] as $field => $label) {
            if ($ref = AdminResources::reference(static::$resourceKey, $field)) {
                $component = Select::make($field)->options(fn () => $ref::where('active', true)->pluck('name', 'id')->all())->searchable();
            } elseif ($field === 'roles') {
                $component = Select::make($field)->multiple()->options(fn () => Role::pluck('name', 'id')->all())->required();
            } elseif (in_array($field, ['active', 'is_deposit'])) {
                $component = Toggle::make($field)->default($field === 'active');
            } else {
                $component = TextInput::make($field)->maxLength(255);
                if ($field === 'password') {
                    $component->password()->revealable()->minLength(12)->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state));
                }
                if ($field === 'email') {
                    $component->email();
                }
                if (in_array($field, ['code', 'name', 'first_name', 'last_name'])) {
                    $component->required();
                }
            }
            $components[] = $component->label($label);
        }
        $components[] = Textarea::make('reason')->label('Motivo del registro o cambio')->required()->maxLength(2000)->columnSpanFull();

        return $schema->components($components)->columns(2);
    }

    public static function table(Table $table): Table
    {
        $columns = [];
        foreach (AdminResources::get(static::$resourceKey)[3] as $field => $label) {
            if (in_array($field, ['password', 'roles'])) {
                continue;
            }
            if ($ref = AdminResources::reference(static::$resourceKey, $field)) {
                $column = TextColumn::make($field)->formatStateUsing(fn ($state) => $ref::find($state)?->name ?? '—');
            } elseif (in_array($field, ['active', 'is_deposit'])) {
                $column = IconColumn::make($field)->boolean();
            } else {
                $column = TextColumn::make($field)->searchable()->sortable()->limit(60);
            }
            $columns[] = $column->label($label);
        }

        return $table->columns($columns)->recordActions([EditAction::make()])->defaultSort('id', 'desc');
    }
}
