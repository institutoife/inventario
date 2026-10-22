<?php

namespace App\Filament\Resources\Assets\RelationManagers;

use App\Filament\Tables\AuditLogsTable;
use App\Models\AuditLog;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $title = 'Historial de cambios';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (auth()->user()?->can('view', $ownerRecord) ?? false) && (auth()->user()?->can('viewAny', AuditLog::class) ?? false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return AuditLogsTable::configure($table)->modifyQueryUsing(fn (Builder $query) => $query->with('user'));
    }
}
