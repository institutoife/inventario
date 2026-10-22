<?php

namespace App\Filament\Widgets;

use App\Queries\AssetVisibility;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return auth()->user()?->can('assets.view') ?? false;
    }

    protected function getStats(): array
    {
        $query = AssetVisibility::forUser(auth()->user());

        return [
            Stat::make('Activos registrados', (clone $query)->count()),
            Stat::make('Con responsable', (clone $query)->whereNotNull('current_person_id')->count()),
            Stat::make('En depósito', (clone $query)->whereHas('administrativeStatus', fn ($query) => $query->where('code', 'deposit'))->count()),
            Stat::make('Faltantes', (clone $query)->whereHas('administrativeStatus', fn ($query) => $query->where('code', 'missing'))->count()),
        ];
    }
}
