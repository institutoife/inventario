<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Audit;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AuditAuthentication
{
    public function handle(Login|Logout $event): void
    {
        if ($event->user instanceof User) {
            Audit::record($event instanceof Login ? 'login' : 'logout', 'users', $event->user->id, actorId: $event->user->id);
        }
    }
}
