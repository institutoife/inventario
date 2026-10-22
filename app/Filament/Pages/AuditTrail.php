<?php

namespace App\Filament\Pages;

use App\Filament\Tables\AuditLogsTable;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class AuditTrail extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Auditoría';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.pages.audit-trail';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', AuditLog::class) ?? false;
    }

    public function table(Table $table): Table
    {
        Gate::authorize('viewAny', AuditLog::class);

        return AuditLogsTable::configure($table)->query(AuditLog::query()->with('user'));
    }
}
