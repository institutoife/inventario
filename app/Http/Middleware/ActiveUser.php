<?php

namespace App\Http\Middleware;

class ActiveUser
{
    public function handle($request, \Closure $next)
    {
        if ($request->user() && ! $request->user()->active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('filament.admin.auth.login');
        }

        return $next($request);
    }
}
