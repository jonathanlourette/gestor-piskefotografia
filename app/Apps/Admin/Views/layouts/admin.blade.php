<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <title>@yield('title', 'Admin - Piske Memórias')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { min-height: 100vh; background-color: #f5f6fa; }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .sidebar .nav-link {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.08);
            color: #ffffff;
            transform: translateX(4px);
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            color: #ffffff;
            border-left: 3px solid #667eea;
        }
        .sidebar .nav-link i { font-size: 1.125rem; }
        .sidebar-logo { border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .content-wrapper {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Mobile Header -->
    <header class="topbar d-flex d-lg-none align-items-center px-3 py-3 sticky-top">
        <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="ms-3 fw-bold text-dark">Piske Admin</span>
        <div class="ms-auto d-flex align-items-center gap-2">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">PA</div>
        </div>
    </header>

    <!-- Sidebar (Desktop) + Offcanvas (Mobile) -->
    <nav class="sidebar text-white p-3 offcanvas-lg offcanvas-start" id="sidebarOffcanvas" tabindex="-1">
        <div class="sidebar-logo">
            <a href="{{ route('admin.dashboard.index') }}" class="text-white text-decoration-none d-flex align-items-center gap-2">
                <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" height="36" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="h5 mb-0 fw-semibold d-none" style="display: none;">Piske Admin</span>
            </a>
        </div>

        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard.index') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Produtos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Pedidos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.gallery.index') }}" class="nav-link {{ request()->routeIs('admin.gallery*') ? 'active' : '' }}">
                    <i class="bi bi-images"></i>
                    <span>Galerias</span>
                </a>
            </li>
        </ul>

        <div class="position-absolute bottom-0 start-0 end-0 p-3">
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100 rounded-4 py-2 fw-medium">
                    <i class="bi bi-box-arrow-left me-2"></i>Sair
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar (Desktop) -->
        <header class="topbar d-none d-lg-flex align-items-center px-4 py-3">
            <nav aria-label="breadcrumb" class="flex-grow-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}" class="text-secondary text-decoration-none">Admin</a></li>
                    <li class="breadcrumb-item active text-dark fw-medium">{{ request()->segment(2) ?? 'Dashboard' }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-semibold text-dark">Piske Admin</span>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">PA</div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    <!-- Alpine.js Global: Toast System -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Sistema Global de Toasts
            window.toastSystem = () => ({
                toasts: [],
                counter: 0,
                maxToasts: 3,

                init() {
                    // Listen for toast events
                    window.addEventListener('toast', (e) => {
                        this.add(e.detail.type, e.detail.message);
                    });

                    // Check for flash messages on load
                    @if(session('success'))
                        this.add('success', @json(session('success')));
                    @endif
                    @if(session('error'))
                        this.add('error', @json(session('error')));
                    @endif
                    @if(session('warning'))
                        this.add('warning', @json(session('warning')));
                    @endif
                },

                add(type, message) {
                    // Limit to max 3 toasts
                    if (this.toasts.length >= this.maxToasts) {
                        this.toasts.shift();
                    }

                    const id = this.counter++;
                    this.toasts.push({ id, type, message });

                    // Auto-remove after 4 seconds
                    setTimeout(() => {
                        this.remove(id);
                    }, 4000);
                },

                remove(id) {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                },

                getIcon(type) {
                    switch(type) {
                        case 'success': return 'bi-check-circle-fill';
                        case 'error': return 'bi-exclamation-triangle-fill';
                        case 'warning': return 'bi-exclamation-circle-fill';
                        default: return 'bi-info-circle-fill';
                    }
                },

                getBgClass(type) {
                    switch(type) {
                        case 'success': return 'bg-success';
                        case 'error': return 'bg-danger';
                        case 'warning': return 'bg-warning';
                        default: return 'bg-primary';
                    }
                }
            });
        });
    </script>

    <!-- Toast Container Global -->
    <div x-data="toastSystem()" x-init="init()" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast align-items-center text-white border-0 shadow-lg"
                 :class="getBgClass(toast.type)"
                 role="alert" aria-live="assertive" aria-atomic="true"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-y-[-100%] opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="translate-y-[-20px] opacity-0">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i :class="`bi ${getIcon(toast.type)} fs-5`"></i>
                        <span x-text="toast.message"></span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="remove(toast.id)" aria-label="Fechar"></button>
                </div>
            </div>
        </template>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalMessage" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-body p-4">
                    <p class="fw-semibold text-dark mb-0" id="confirmModalMessage"></p>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                    <button type="button" class="btn btn-light rounded-4 fw-medium flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-4 fw-medium flex-grow-1" id="confirmModalOk">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.confirmModal = function (message) {
            return new Promise((resolve) => {
                const modalEl = document.getElementById('confirmModal');
                const okBtn = document.getElementById('confirmModalOk');

                document.getElementById('confirmModalMessage').textContent = message;

                const modal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

                const onOk = () => { cleanup(); modal.hide(); resolve(true); };
                const onCancel = () => { cleanup(); modal.hide(); resolve(false); };
                const cleanup = () => {
                    okBtn.removeEventListener('click', onOk);
                    modalEl.removeEventListener('hidden.bs.modal', onCancel);
                };

                okBtn.addEventListener('click', onOk);
                modalEl.addEventListener('hidden.bs.modal', onCancel, { once: true });

                modal.show();
            });
        };
    </script>

    @stack('scripts')
</body>
</html>
