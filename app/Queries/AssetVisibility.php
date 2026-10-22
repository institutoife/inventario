<?php

namespace App\Queries;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AssetVisibility
{
    public static function forUser(?User $user, ?Builder $query = null): Builder
    {
        $query ??= Asset::query();

        // Current RBAC grants global visibility. Future institutional scopes belong here.
        return $user?->hasPermission('assets.view') ? $query : $query->whereRaw('1 = 0');
    }
}
