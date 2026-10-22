<?php

namespace Tests\Feature\Filament;

use App\Models\AuditLog;
use Filament\Auth\Pages\Login;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

class PublicHomeTest extends FilamentTestCase
{
    public function test_guest_home_is_public_and_links_to_filament_login(): void
    {
        Auth::logout();
        $this->get('/')->assertOk()->assertViewIs('home')
            ->assertSee('Iniciar sesión')->assertSee(route('filament.admin.auth.login'), false)
            ->assertDontSee('Ir al panel')->assertDontSee($this->operator->email);
        $this->get('/login')->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_login_preserves_the_requested_private_destination_after_visiting_home(): void
    {
        Auth::logout();
        $this->get('/panel/assets')->assertRedirect(route('filament.admin.auth.login'));
        $this->get('/')->assertOk();
        Livewire::test(Login::class)->fillForm(['email' => $this->operator->email, 'password' => 'password'])
            ->call('authenticate')->assertHasNoFormErrors()->assertRedirect(url('/panel/assets'));
    }

    public function test_authenticated_home_and_logout_return_to_public_page(): void
    {
        $this->get('/')->assertOk()->assertSee('Ir al panel')->assertSee('Cerrar sesión')->assertDontSee('Iniciar sesión');
        $this->get('/login')->assertRedirect(route('filament.admin.pages.dashboard'));
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
        $this->get('/')->assertOk()->assertSee('Iniciar sesión')->assertDontSee('Ir al panel');
        $this->assertSame(1, AuditLog::where('action', 'logout')->count());
    }

    public function test_legacy_login_rejects_accounts_without_panel_permission(): void
    {
        Auth::logout();
        $user = $this->makeUser(null);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, AuditLog::where('action', 'login')->count());
    }
}
