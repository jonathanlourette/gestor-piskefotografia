<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <title>@yield('title', 'Piske Memórias')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

@stack('styles')
<style>
    html {
        scroll-behavior: smooth;
    }
    .position-transparent {
        background-color: transparent !important;
    }
    .position-transparent.navbar-light .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .position-transparent.navbar-light .nav-link:hover {
        color: #ffffff !important;
    }
    .floating-cart-btn {
        top: 0.75rem;
        right: 1.5rem;
        width: 56px;
        height: 56px;
        z-index: 1035;
        transition: transform 0.2s ease;
    }
    .floating-cart-btn:hover {
        transform: scale(1.08);
    }
    @media (max-width: 991.98px) {
        #mainNavbar .navbar-toggler {
            margin-right: 4rem;
        }
    }
</style>
</head>
<body>
    <!-- Header -->
    @php($headerSolid = (bool)($__env->yieldContent('header_solid')))
    <header
        id="mainNavbar"
        class="navbar navbar-expand-lg navbar-light position-fixed w-100 transition-all"
        style="z-index: 10; top: 0; transition: all 0.3s ease;"
        x-data="{ scrolled: window.scrollY > 50, hideAtTop: {{ $headerSolid ? 'true' : 'false' }} }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 50; })"
        :class="scrolled ? 'bg-white shadow-sm' : 'bg-transparent'"
        :style="hideAtTop && !scrolled ? 'transform: translateY(-110%);' : ''"
    >
        <div class="container-fluid px-4 px-lg-5">
            <a class="navbar-brand fw-bold" href="{{ route('site.landing.index') }}">
                <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" style="height: 40px;">
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-controls="mobileNavOffcanvas">
                <i class="bi bi-list fs-3" :class="scrolled ? 'text-dark' : 'text-white'"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-4">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" :class="scrolled ? 'text-dark' : 'text-white'" href="#hero">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" :class="scrolled ? 'text-dark' : 'text-white'" href="#como-funciona">Como Funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" :class="scrolled ? 'text-dark' : 'text-white'" href="#pacotes">Pacotes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" :class="scrolled ? 'text-dark' : 'text-white'" href="#faq">FAQ</a>
                    </li>
                </ul>

            </div>
        </div>
    </header>

    <!-- Botão Flutuante do Carrinho -->
    <button
        type="button"
        class="btn btn-dark rounded-circle shadow-lg position-fixed d-flex align-items-center justify-content-center floating-cart-btn"
        data-bs-toggle="offcanvas"
        data-bs-target="#cartOffcanvas"
        aria-controls="cartOffcanvas"
        aria-label="Abrir carrinho"
        x-data="{ cartCount: 0 }"
        @cart-badge-update.window="cartCount = $event.detail.count"
    >
        <i class="bi bi-cart3 fs-4"></i>
        <span class="badge bg-danger position-absolute rounded-pill" style="top: -4px; right: -4px; display: none;" x-show="cartCount > 0" x-text="cartCount"></span>
    </button>

    <!-- Mobile Navigation Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavOffcanvas" aria-labelledby="mobileNavOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="mobileNavOffcanvasLabel">
                <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" style="height: 30px;">
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="navbar-nav">
                <li class="nav-item mb-3">
                    <a class="nav-link fs-5 fw-semibold" href="#hero" data-bs-dismiss="offcanvas">Início</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fs-5 fw-semibold" href="#como-funciona" data-bs-dismiss="offcanvas">Como Funciona</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fs-5 fw-semibold" href="#pacotes" data-bs-dismiss="offcanvas">Pacotes</a>
                </li>
                <li class="nav-item mb-3">
                    <a class="nav-link fs-5 fw-semibold" href="#faq" data-bs-dismiss="offcanvas">FAQ</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-5 text-white" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);">
        <div class="container-fluid px-4 px-lg-5">
            <div class="row g-5">
                <!-- Sobre -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <img src="{{ asset('assets/img/logo-piske.png') }}" alt="Piske Memórias" style="height: 40px;">
                        <h5 class="fw-bold mb-0">Piske Memórias</h5>
                    </div>
                    <p class="text-white-50 mb-0">
                        Transformando suas memórias digitais em impressões de qualidade profissional para guardar para sempre. Cada foto conta uma história.
                    </p>
                </div>

                <!-- Contato -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h6 class="fw-bold text-white mb-4">Contato</h6>
                    <ul class="list-unstyled text-white-50 mb-0 space-y-3">
                        <li class="mb-3 d-flex align-items-center gap-3">
                            <div class="bg-white-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1);">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <a href="mailto:piskefotografia@gmail.com" class="text-white-50 text-decoration-none hover-white">piskefotografia@gmail.com</a>
                        </li>
                        <li class="mb-3 d-flex align-items-center gap-3">
                            <div class="bg-white-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1);">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <a href="https://wa.me/5527997812533" target="_blank" class="text-white-50 text-decoration-none hover-white">(27) 99781-2533</a>
                        </li>
                    </ul>
                </div>

                <!-- Redes Sociais -->
                <div class="col-lg-4">
                    <h6 class="fw-bold text-white mb-4">Redes Sociais</h6>
                    <div class="d-flex gap-3">
                        <a href="https://instagram.com/piskefotografia" target="_blank" class="bg-white-10 rounded-circle d-flex align-items-center justify-content-center text-white-50 hover-white transition-all" style="width: 48px; height: 48px; background: rgba(255,255,255,0.1);">
                            <i class="bi bi-instagram fs-4"></i>
                        </a>
                        <a href="https://facebook.com/piskefotografia" target="_blank" class="bg-white-10 rounded-circle d-flex align-items-center justify-content-center text-white-50 hover-white transition-all" style="width: 48px; height: 48px; background: rgba(255,255,255,0.1);">
                            <i class="bi bi-facebook fs-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-5 border-secondary" style="opacity: 0.3;">

            <div class="text-center text-white-50 small">
                <p class="mb-2">© {{ date('Y') }} Piske Memórias. Todos os direitos reservados.</p>
                <p class="mb-0">Feito com ❤️ para guardar suas memórias</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @stack('scripts')

    <!-- Offcanvas do Carrinho -->
    @include('site::cart.offcanvas')

    <!-- Modal de Confirmação Global -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalMessage" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-body text-center p-4 pb-3">
                    <div class="bg-danger-subtle text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <p class="fw-semibold text-dark mb-0" id="confirmModalMessage"></p>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-4 fw-medium flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger rounded-4 fw-medium flex-grow-1" id="confirmModalOk">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Global: Carrinho e Toast System -->
    <script>
        // Confirmação via modal (substitui o confirm() nativo)
        window.confirmModal = function (message) {
            return new Promise((resolve) => {
                const modalEl = document.getElementById('confirmModal');
                const okBtn = document.getElementById('confirmModalOk');
                document.getElementById('confirmModalMessage').textContent = message;

                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                let confirmed = false;

                const onOk = () => {
                    confirmed = true;
                    modal.hide();
                };
                okBtn.addEventListener('click', onOk, { once: true });

                modalEl.addEventListener('hidden.bs.modal', () => {
                    okBtn.removeEventListener('click', onOk);
                    resolve(confirmed);
                }, { once: true });

                modal.show();
            });
        };

        // Função global para adicionar ao carrinho via AJAX
        async function addToCart(productId, productName, btnElement) {
            const originalHTML = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Adicionando...';

            try {
                const response = await fetch('{{ route("site.cart.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const data = await response.json();

                if (data.success) {
                    // Atualiza badge do carrinho
                    window.dispatchEvent(new CustomEvent('cart-badge-update', { detail: { count: data.count } }));

                    // Força offcanvas do carrinho a recarregar os dados
                    window.dispatchEvent(new CustomEvent('cart-updated'));

                    // Exibe toast de sucesso
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'success', message: productName + ' adicionado ao carrinho!' }
                    }));

                    // Abre o offcanvas do carrinho para o cliente seguir ao checkout
                    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('cartOffcanvas')).show();

                    // Feedback visual no botão
                    btnElement.innerHTML = '<i class="bi bi-check-lg"></i> Adicionado ao carrinho!';
                    btnElement.classList.add('btn-success');
                    btnElement.classList.remove('btn-dark', 'btn-outline-dark');

                    setTimeout(() => {
                        btnElement.innerHTML = originalHTML;
                        btnElement.classList.remove('btn-success');
                        // Restaura classe original
                        if (originalHTML.includes('btn-outline-dark')) {
                            btnElement.classList.add('btn-outline-dark');
                        } else {
                            btnElement.classList.add('btn-dark');
                        }
                        btnElement.disabled = false;
                    }, 2500);
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', message: data.message || 'Erro ao adicionar produto.' }
                    }));
                    btnElement.innerHTML = originalHTML;
                    btnElement.disabled = false;
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', message: 'Erro de conexão. Tente novamente.' }
                }));
                btnElement.innerHTML = originalHTML;
                btnElement.disabled = false;
            }
        }

        document.addEventListener('alpine:init', () => {
            // Estado global do carrinho para compartilhar entre componentes
            window.cartData = {
                count: 0
            };

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
</body>
</html>
