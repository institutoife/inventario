<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Assets\AssetResource;
use App\Models\Permission;
use App\Models\Role;

class AccessBoundaryTest extends FilamentTestCase
{
    public function test_update_permission_alone_cannot_disclose_legacy_asset_form(): void
    {
        $asset = $this->makeAsset();
        $role = Role::create(['code' => 'update-only', 'name' => 'Solo permiso de edición']);
        $role->permissions()->attach(Permission::where('code', 'assets.update')->sole());
        $user = $this->makeUser(null);
        $user->roles()->attach($role);
        $this->actingAs($user)->get('/admin/assets/'.$asset->id.'/edit')->assertForbidden();
        $this->get(AssetResource::getUrl('view', ['record' => $asset]))->assertForbidden();
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_catalog_operator_can_enter_panel_without_access_to_assets_or_statistics(): void
    {
        $role = Role::create(['code' => 'catalog-only', 'name' => 'Catálogos']);
        $role->permissions()->attach(Permission::where('code', 'catalogs.manage')->sole());
        $user = $this->makeUser(null);
        $user->roles()->attach($role);
        $this->actingAs($user)->get('/panel')->assertOk()->assertSee('Catálogos')->assertDontSee('Activos registrados');
        $this->get(AssetResource::getUrl())->assertForbidden();
        $this->get('/admin/assets')->assertForbidden();
    }
}
