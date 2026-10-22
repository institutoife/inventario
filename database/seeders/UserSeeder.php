<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $settings = config('initial_admin');
        $email = $settings['email'] ?? null;
        if (blank($email)) {
            if (! app()->isLocal()) {
                $this->command?->warn('Configure INITIAL_ADMIN_EMAIL y INITIAL_ADMIN_PASSWORD para crear el administrador.');

                return;
            }
            $email = 'admin@inventario.local';
        }
        if (User::where('email', $email)->exists()) {
            $this->command?->info('El usuario '.$email.' ya existe; no se modificaron su contraseña ni sus roles.');

            return;
        }

        $password = $settings['password'] ?? null;
        $generatedPassword = blank($password) && app()->isLocal();
        if ($generatedPassword) {
            $password = Str::random(24);
        }
        $data = Validator::make([
            'name' => $settings['name'] ?? 'Administrador',
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:12|max:255',
        ])->validate();

        $created = DB::transaction(function () use ($data): bool {
            // A repeated execution must not elevate accounts or reset credentials.
            if (User::where('email', $data['email'])->exists()) {
                return false;
            }
            $role = Role::where('code', 'superadmin')->sole();
            $user = User::create([...$data, 'active' => true]);
            $user->roles()->attach($role);
            Audit::record('bootstrap', 'users', $user->id, [], [
                ...$user->toArray(), 'roles' => [$role->id],
            ], 'Creación del administrador mediante UserSeeder');

            return true;
        });

        if ($created) {
            $this->command?->info('Administrador creado: '.$data['email']);
            if ($generatedPassword) {
                $this->command?->line('Contraseña inicial: '.$data['password']);
            }
        }
    }
}
