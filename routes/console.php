<?php

Artisan::command('app:create-admin {email} {name}', function () {
    $password = $this->secret('Contraseña (mínimo 12 caracteres)');
    if (strlen((string) $password) < 12 || ! filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL)) {
        $this->error('Correo o contraseña inválidos.');

        return 1;
    }
    DB::transaction(function () use ($password) {
        $user = User::create(['name' => $this->argument('name'), 'email' => $this->argument('email'), 'password' => $password, 'active' => true]);
        $user->roles()->attach(Role::where('code', 'superadmin')->sole());
        Audit::record('bootstrap', 'users', $user->id, [], $user->toArray(), 'Creación administrativa mediante consola');
    });
    $this->info('Administrador creado.');
})->purpose('Crear un administrador sin contraseñas predeterminadas');

use App\Models\Role;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
