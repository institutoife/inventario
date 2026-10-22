<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoundationController;
use App\Http\Middleware\ActiveUser;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.submit');
});
Route::middleware(['auth', ActiveUser::class])->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/audit', function () {
        Gate::authorize('audit.view');

        return view('audit', ['logs' => AuditLog::latest('id')->paginate(30)]);
    })->name('audit');
    Route::get('/roles', function () {
        Gate::authorize('users.manage');

        return view('roles', ['roles' => Role::with('permissions')->get(), 'permissions' => Permission::all()]);
    })->name('roles');
    Route::put('/roles/{role}', function (Request $request, Role $role) {
        Gate::authorize('users.manage');
        abort_if($role->code === 'superadmin', 403);
        $data = $request->validate(['permissions' => 'sometimes|array', 'permissions.*' => 'integer|distinct|exists:permissions,id', 'reason' => 'required|string|max:2000']);
        DB::transaction(function () use ($role, $data) {
            $role = Role::lockForUpdate()->findOrFail($role->id);
            $old = $role->permissions()->pluck('permissions.id')->all();
            $role->permissions()->sync($data['permissions'] ?? []);
            Audit::record('permissions.update', 'roles', $role->id, ['permissions' => $old], ['permissions' => $data['permissions'] ?? []], $data['reason']);
        });

        return redirect()->route('roles')->with('status', 'Permisos actualizados.');
    })->name('roles.save');
    Route::get('/admin/{resource}', [FoundationController::class, 'index'])->name('admin.index');
    Route::get('/admin/{resource}/create', [FoundationController::class, 'form'])->name('admin.create');
    Route::get('/admin/{resource}/{id}/edit', [FoundationController::class, 'form'])->whereNumber('id')->name('admin.edit');
    Route::post('/admin/{resource}', [FoundationController::class, 'save'])->name('admin.store');
    Route::put('/admin/{resource}/{id}', [FoundationController::class, 'save'])->whereNumber('id')->name('admin.update');
});
