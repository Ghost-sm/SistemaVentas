<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="BRYSA: ventas, inventario y reposición inteligente para tiendas de abarrotes" />
    <title>BRYSA - Iniciar sesión</title>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --navy: #212529;
            --navy-2: #2B3035;
            --orange: #0D6EFD;
            --orange-dark: #0B5ED7;
            --paper: #F8F9FA;
            --ink: #1B1F2A;
            --muted: #5A6172;
        }

        html, body { height: 100%; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }

        .auth { display: flex; min-height: 100vh; }

        /* Panel de marca */
        .brand {
            flex: 1.2;
            background: radial-gradient(circle at 80% 20%, #343A40 0%, var(--navy) 55%, #16191C 100%);
            color: #F6F3EC;
            padding: 56px 64px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .brand::after {
            content: "";
            position: absolute;
            right: -120px;
            bottom: -120px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 110, 253, .25) 0%, rgba(13, 110, 253, 0) 70%);
        }
        .logo { display: flex; align-items: center; gap: 12px; font-weight: 800; letter-spacing: 2px; }
        .logo-badge {
            width: 44px; height: 44px; border-radius: 12px;
            background: var(--orange); color: #FFFFFF;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .brand h1 { font-size: 64px; font-weight: 800; line-height: 1; margin: 0 0 18px; }
        .brand h1 span { color: var(--orange); }
        .brand .rule { width: 72px; height: 4px; border-radius: 4px; background: var(--orange); margin-bottom: 20px; }
        .brand .tagline { font-size: 22px; line-height: 1.4; max-width: 460px; color: #DDE2EC; margin: 0; }
        .brand .tagline b { color: #F6F3EC; }


        .features { display: flex; gap: 24px; flex-wrap: wrap; font-size: 14px; color: #C9CFDC; position: relative; z-index: 1; }
        .features span { display: flex; align-items: center; gap: 8px; }
        .features svg { color: var(--orange); }

        /* Panel de acceso */
        .access { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
        .access-card {
            width: 100%; max-width: 420px;
            background: #FFFFFF; border-radius: 20px;
            box-shadow: 0 20px 50px rgba(33, 37, 41, .12);
            padding: 40px 36px;
        }
        .access-card h2 { font-size: 28px; font-weight: 800; color: var(--navy); margin: 0 0 6px; }
        .access-card .sub { color: var(--muted); margin: 0 0 28px; }
        .access-card .form-control { border-radius: 12px; border-color: #CED4DA; }
        .access-card .form-control:focus { border-color: var(--orange); box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .25); }
        .btn-brysa {
            width: 100%; border: 0; border-radius: 12px; padding: 14px;
            background: var(--orange); color: #FFFFFF; font-weight: 700; font-size: 16px;
            transition: background .2s;
        }
        .btn-brysa:hover, .btn-brysa:focus { background: var(--orange-dark); color: #FFFFFF; }
        .access-foot { text-align: center; color: var(--muted); font-size: 13px; margin-top: 24px; }

        @media (max-width: 900px) {
            .brand { display: none; }
            .access-card .mobile-logo { display: flex !important; }
        }
    </style>
</head>

<body>
    <div class="auth">
        <section class="brand" aria-hidden="true">
            <div class="logo">
                <div class="logo-badge"><i class="fa-solid fa-basket-shopping"></i></div>
                BRYSA
            </div>

            <div>
                <h1>BRYS<span>A</span></h1>
                <div class="rule"></div>
                <p class="tagline">Ventas, inventario y <b>reposición inteligente</b> para tiendas de abarrotes.</p>
            </div>

            <div class="features">
                <span><i class="fa-solid fa-cash-register"></i> Ventas y caja</span>
                <span><i class="fa-solid fa-boxes-stacked"></i> Inventario y kardex</span>
                <span><i class="fa-solid fa-brain"></i> Sugerencias con IA</span>
            </div>
        </section>

        <main class="access">
            <div class="access-card">
                <div class="logo mobile-logo mb-4" style="display:none;color:#212529">
                    <div class="logo-badge"><i class="fa-solid fa-basket-shopping"></i></div>
                    BRYSA
                </div>

                <h2>Bienvenido</h2>
                <p class="sub">Ingresa con tu correo y contraseña.</p>

                @if ($errors->any())
                @foreach ($errors->all() as $item)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $item }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
                @endforeach
                @endif

                <form action="{{ route('login.login') }}" method="post">
                    @csrf
                    <div class="form-floating mb-3">
                        <input autofocus autocomplete="username" value="{{ old('email') }}" class="form-control" name="email" id="inputEmail" type="email" placeholder="correo@ejemplo.com" required />
                        <label for="inputEmail"><i class="fa-regular fa-envelope me-1"></i> Correo electrónico</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input class="form-control" name="password" id="inputPassword" type="password" placeholder="Contraseña" autocomplete="current-password" required />
                        <label for="inputPassword"><i class="fa-solid fa-lock me-1"></i> Contraseña</label>
                    </div>
                    <button class="btn-brysa" type="submit">Iniciar sesión</button>
                </form>

                <p class="access-foot">© {{ date('Y') }} BRYSA · Yumbo, Valle del Cauca</p>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>
