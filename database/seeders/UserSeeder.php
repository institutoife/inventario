<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Edita aquí las credenciales antes de ejecutar php artisan db:seed.
        $name = 'Administrador'; 
        $email = 'admin@acho.com';
        $password = 'CambiarEstaClave2026!';

        if (User::where('email', $email)->exists()) {
            $this->command?->info('El usuario '.$email.' ya existe; no se modificaron su contraseña ni sus roles.');

            return;
        }

        $data = Validator::make([
            'name' => $name,
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

        }
    }
}
