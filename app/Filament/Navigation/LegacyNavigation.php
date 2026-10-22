<?php

namespace App\Filament\Navigation;

use App\Services\AdminResources;
use Filament\Navigation\NavigationItem;

class LegacyNavigation
{
    public static function items(): array
    {
        $items = [];
        foreach (AdminResources::all() as $key => $config) {
            if ($key === 'assets') {
                continue;
            }
            $group = match ($key) {
                'people' => 'Inventario',
                'units', 'offices', 'locations' => 'Organización',
                default => 'Administración',
            };
            $item = NavigationItem::make($config[1])->group($group)->sort(20)
                ->url(fn () => route('admin.index', $key))
                ->visible(fn () => auth()->user()?->can($config[2]) ?? false);
            if ($group === 'Administración' && $key !== 'users') {
                $item->parentItem('Catálogos');
            } else {
                $item->icon(match ($key) {
                    'people', 'users' => 'heroicon-o-users',
                    'units' => 'heroicon-o-building-office-2',
                    default => 'heroicon-o-map-pin',
                });
            }
            $items[] = $item;
        }
        $items[] = NavigationItem::make('Roles y permisos')->group('Administración')->sort(21)
            ->icon('heroicon-o-key')->url(fn () => route('roles'))
            ->visible(fn () => auth()->user()?->can('users.manage') ?? false);
        $items[] = NavigationItem::make('Catálogos')->group('Administración')->sort(30)
            ->icon('heroicon-o-rectangle-stack')->url(fn () => route('admin.index', 'budget-items'))
            ->visible(fn () => auth()->user()?->can('catalogs.manage') ?? false);

        return $items;
    }
}
