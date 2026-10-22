<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class EnsurePanelAccess
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->canAccessPanel(Filament::getCurrentOrDefaultPanel()), 403);

        return $next($request);
    }
}
