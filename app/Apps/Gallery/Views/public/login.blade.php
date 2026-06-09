@php
    /** @var \App\Domains\Gallery\Gallery $gallery */
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <title>{{ $gallery->title }} — Piske Memórias</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --gallery-bg: #fafafa;
            --gallery-text: #1a1a1a;
            --gallery-text-muted: #6b7280;
            --gallery-accent: #2563eb;
            --gallery-radius: 0.75rem;
            --gallery-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            min-height: 100vh;
            background: linear-gradient(180deg, #ffffff 0%, var(--gallery-bg) 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--gallery-text);
        }

        .login-card {
            max-width: 400px;
            width: 100%;
            border: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: var(--gallery-shadow);
        }

        .login-card .form-control {
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .login-card .form-control:focus {
            border-color: var(--gallery-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-gallery {
            background-color: var(--gallery-text);
            color: #ffffff;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-gallery:hover {
            background-color: #333333;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gallery-text-muted);
            cursor: pointer;
            padding: 0;
            transition: color 0.2s ease;
        }

        .password-wrapper .toggle-password:hover {
            color: var(--gallery-text);
        }

        .brand-footer {
            color: var(--gallery-text-muted);
            font-size: 0.8rem;
        }

        .brand-footer a {
            color: var(--gallery-text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .brand-footer a:hover {
            color: var(--gallery-text);
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3 py-5">
        <div class="login-card bg-white rounded-5 p-5 text-center" x-data="{ showPassword: false }">

            <!-- Logo -->
            <div class="mb-4">
                <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" style="height: 40px;" class="mb-3">
            </div>

            <!-- Gallery Title -->
            <h1 class="fw-bold mb-2" style="font-size: 1.5rem; letter-spacing: -0.02em; color: var(--gallery-text);">
                {{ $gallery->title }}
            </h1>
            <p class="text-body-secondary mb-4 fs-6">
                Esta galeria é protegida por senha
            </p>

            <!-- Error Message -->
            @if(session('error'))
                <div class="alert alert-error rounded-4 d-flex align-items-center gap-2 mb-4 py-3 px-3 border-0">
                    <i class="bi bi-shield-exclamation fs-5"></i>
                    <span class="small">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('gallery.auth', $gallery->uuid) }}" method="POST" class="text-start">
                @csrf

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small text-body-secondary mb-2">Senha de acesso</label>
                    <div class="password-wrapper">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            id="password"
                            class="form-control form-control-lg rounded-4 py-3 pe-5"
                            placeholder="Digite a senha"
                            required
                            autocomplete="current-password"
                            autofocus
                        >
                        <button type="button" class="toggle-password" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'">
                            <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'" style="font-size: 1.15rem;"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-gallery btn-lg rounded-4 w-100 py-3 fw-semibold">
                    Entrar
                </button>
            </form>

            <!-- Branding -->
            <div class="brand-footer mt-4 pt-3">
                powered by <a href="{{ route('site.landing.index') }}">Piske Memórias</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
</body>
</html>
