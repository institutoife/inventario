<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema institucional para la gestión y consulta de activos fijos.">
    <title>Activos fijos | Inicio</title>
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body>
    <a class="skip-link" href="#contenido">Ir al contenido</a>
    <header class="site-header">
        <div class="header-content">
            <a class="brand" href="{{ route('home') }}" aria-label="Activos fijos, inicio">
                <span class="brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h16v13H4zM3 3h18v4H3zM9 11h6"/>
                    </svg>
                </span>
                <span>Activos fijos<small>Sistema institucional</small></span>
            </a>
            <nav class="account-actions" aria-label="Acceso de usuario">
                @auth
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    @if(auth()->user()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
                        <a class="button button-primary" href="{{ route('filament.admin.pages.dashboard') }}">Ir al panel</a>
                    @endif
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="button button-icon" type="submit" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 4H4v16h5M14 8l4 4-4 4M8 12h10"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <a class="button button-primary" href="{{ route('filament.admin.auth.login') }}">Iniciar sesión</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="contenido" class="main-content">
        <section class="welcome" aria-labelledby="welcome-title">
            <p class="eyebrow">Control y organización institucional</p>
            <h1 id="welcome-title">Gestión de<br>activos fijos.</h1>
            <p class="intro">Un espacio para organizar, consultar y dar seguimiento a los bienes de la institución.</p>
            <p class="access-note">Ingresa con tu cuenta institucional para acceder a tus herramientas de trabajo.</p>
        </section>

        <section class="features" aria-label="Herramientas del sistema">
            <div>
                <h2>Inventario</h2>
                <p>Información de los activos, su ubicación y estado.</p>
            </div>
            <div>
                <h2>Organización</h2>
                <p>Unidades, oficinas y responsables en un solo lugar.</p>
            </div>
            <div>
                <h2>Trazabilidad</h2>
                <p>Registro de cambios para facilitar el seguimiento.</p>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>Sistema institucional de activos fijos</p>
        <span>Gestión y control patrimonial</span>
    </footer>
</body>
</html>
