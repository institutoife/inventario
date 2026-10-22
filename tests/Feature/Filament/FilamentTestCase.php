<?php

namespace Tests\Feature\Filament;

use App\Models\AdministrativeStatus;
use App\Models\Asset;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\FoundationSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class FilamentTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FoundationSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        $this->operator = $this->makeUser();
        $this->actingAs($this->operator);
    }

    protected function makeUser(?string $role = 'superadmin'): User
    {
        $user = User::factory()->create();
        if ($role) {
            $user->roles()->attach(Role::where('code', $role)->sole());
        }

        return $user;
    }

    protected function assetData(array $overrides = []): array
    {
        return array_replace([
            'code' => 'AF-001', 'description' => 'Computadora institucional',
            'registered_on' => today()->subDay()->toDateString(), 'reason' => 'Registro de inventario verificado',
        ], $overrides);
    }

    protected function makeAsset(array $overrides = []): Asset
    {
        $data = $this->assetData($overrides);
        unset($data['reason']);

        return Asset::create([...$data, 'administrative_status_id' => AdministrativeStatus::where('code', 'available')->sole()->id]);
    }
}
