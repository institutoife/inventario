<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $data = Validator::make(config('initial_admin'), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:12|max:255',
        ])->validate();

        DB::transaction(function () use ($data) {
            // Never elevate an existing account or reset its credentials implicitly.
            if (User::where('email', $data['email'])->exists()) {
                $this->command?->info('El usuario inicial ya existe; no se modificó.');

                return;
            }

            $role = Role::where('code', 'superadmin')->sole();
            $user = User::create([...$data, 'active' => true]);
            $user->roles()->attach($role);
            Audit::record('bootstrap', 'users', $user->id, [], [
                ...$user->toArray(), 'roles' => [$role->id],
            ], 'Creación del usuario inicial mediante seeder');
        });
    }
}
