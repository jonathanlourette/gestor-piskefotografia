<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <title>@yield('title', 'Login - Piske Memórias')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .login-card {
            backdrop-filter: blur(20px);
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .logo-container {
            animation: fadeInDown 0.6s ease-out;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center p-4">
        <div class="w-100" style="max-width: 420px;">
            <!-- Logo/Header -->
            <div class="text-center mb-5 logo-container">
                <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" height="72" class="mb-3" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="d-none" style="display: none;">
                    <i class="bi bi-camera display-1 text-white mb-2"></i>
                </div>
                <h1 class="h2 text-white fw-bold mb-1">Piske Memórias</h1>
                <p class="text-white-50 fs-6">Painel Administrativo</p>
            </div>

            <!-- Content Card -->
            <div class="login-card rounded-4 p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @stack('scripts')
</body>
</html>