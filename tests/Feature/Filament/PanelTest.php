<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\AuditTrail;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Assets\AssetResource;
use App\Filament\Widgets\AssetStats;
use App\Models\AuditLog;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

class PanelTest extends FilamentTestCase
{
    public function test_guest_is_redirected_to_panel_login(): void
    {
        Auth::logout();
        $this->get('/panel')->assertRedirect('/panel/login');
        $this->get('/panel/login')->assertOk();
    }

    public function test_sidebar_dashboard_and_permitted_navigation_render(): void
    {
        $this->get('/panel')->assertOk()->assertSee('Inventario')->assertSee('Organización')->assertSee('Administración')->assertSee('Activos')->assertSee('Catálogos');
        Livewire::test(Dashboard::class)->assertOk();
        Livewire::test(AssetStats::class)->assertSee('Activos registrados')->assertSee('Con responsable');
        $this->assertFalse(Filament::getCurrentPanel()->hasTopNavigation());
    }

    public function test_user_without_roles_cannot_enter_even_in_local_environment(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs($this->makeUser(null))->get('/panel')->assertForbidden();
        $this->get(AssetResource::getUrl())->assertForbidden();
    }

    public function test_inactive_user_is_denied(): void
    {
        $this->operator->update(['active' => false]);
        $this->get('/panel')->assertForbidden();
    }

    public function test_login_logout_are_audited_exactly_once_and_password_is_not_recorded(): void
    {
        Auth::logout();
        AuditLog::query()->delete(); // SQLite test fixture only, before auditing the login.
        Livewire::test(Login::class)->fillForm(['email' => $this->operator->email, 'password' => 'password'])
            ->call('authenticate')->assertHasNoFormErrors()->assertRedirect(Dashboard::getUrl());
        $this->assertAuthenticatedAs($this->operator);
        $log = AuditLog::where('action', 'login')->sole();
        $this->assertSame($this->operator->id, $log->user_id);
        $this->assertArrayNotHasKey('password', $log->new_values);
        $this->post('/panel/logout')->assertRedirect('/');
        $this->assertSame(1, AuditLog::where('action', 'logout')->count());
        $this->assertGuest();
    }

    public function test_inactive_and_unprivileged_accounts_cannot_login(): void
    {
        Auth::logout();
        $this->operator->update(['active' => false]);
        Livewire::test(Login::class)->fillForm(['email' => $this->operator->email, 'password' => 'password'])
            ->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest();
        $this->assertSame(0, AuditLog::where('action', 'login')->count());
        $user = $this->makeUser(null);
        Livewire::test(Login::class)->fillForm(['email' => $user->email, 'password' => 'password'])
            ->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest();
    }

    public function test_auditor_has_audit_access_but_no_user_management_links(): void
    {
        $this->actingAs($this->makeUser('auditor'));
        $this->get('/panel')->assertOk()->assertDontSee(route('admin.index', 'users'), false);
        $this->get(AuditTrail::getUrl())->assertOk();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_audit_page_and_widgets_deny_users_without_permission(): void
    {
        $this->actingAs($this->makeUser('asset_manager'));
        $this->get(AuditTrail::getUrl())->assertForbidden();
        Livewire::test(AuditTrail::class)->assertForbidden();
        $this->assertFalse(Gate::allows('create', AuditLog::class));
        $this->actingAs($this->makeUser(null));
        $this->assertFalse(AssetStats::canView());
    }

    public function test_audit_page_is_read_only_with_authorized_details(): void
    {
        $log = AuditLog::create(['user_id' => $this->operator->id, 'action' => 'update', 'entity_type' => 'assets', 'old_values' => ['description' => 'Antes'], 'new_values' => ['description' => 'Después']]);
        Livewire::test(AuditTrail::class)->assertCanSeeTableRecords([$log])->assertTableActionDoesNotExist('edit')->assertTableActionDoesNotExist('delete')
            ->mountTableAction('details', $log)->assertActionMounted(TestAction::make('details')->table($log));
        foreach (['update', 'delete', 'forceDelete', 'restore'] as $ability) {
            $this->assertFalse(Gate::allows($ability, $log));
        }
    }
}
