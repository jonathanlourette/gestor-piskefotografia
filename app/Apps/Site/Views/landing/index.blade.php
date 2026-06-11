@extends('site::layouts.site')

@php
    /** @var \Illuminate\Database\Eloquent\Collection $pacotes */
    /** @var \Illuminate\Database\Eloquent\Collection $outros */
@endphp

@section('title', 'Piske Memórias - Tire suas memórias do digital')

@section('content')
    <div x-data="landingPage()" class="container-fluid px-0">

        <!-- Hero Section -->
        <section id="hero" class="position-relative overflow-hidden text-white" style="min-height: 100vh; background-image: url('{{ asset('assets/img/hero.jpg') }}'); background-size: cover; background-position: center;">
            <!-- Cinematic Overlay -->
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);"></div>

            <div class="container-fluid px-4 px-lg-5 position-relative z-1">
                <div class="row align-items-center min-vh-100 py-5">
                    <div class="col-lg-8 mx-auto text-center">
                        <h1 class="display-2 fw-bolder mb-4" style="letter-spacing: -0.03em; line-height: 1.1;">
                            Chega de fotos só no celular
                        </h1>
                        <p class="lead text-white-50 mb-5 fs-5" style="max-width: 650px; margin-left: auto; margin-right: auto;">
                            Transforme suas memórias digitais em fotos impressas com qualidade profissional
                        </p>
                        <a href="#pacotes" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-camera fs-5"></i> Eu quero!
                        </a>
                    </div>
                </div>

                <!-- Animated Scroll Arrow -->
                <div class="position-absolute bottom-0 start-50 translate-middle-x mb-5 text-center">
                    <a href="#sobre" class="text-white text-decoration-none">
                        <div class="bounce">
                            <i class="bi bi-chevron-down fs-3"></i>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Sobre Section - Full-width Split -->
        <section id="sobre" class="bg-white">
            <div class="row g-0">
                <div class="col-md-6">
                    <img src="{{ asset('assets/img/corte1.jpg') }}" alt="Sobre Piske Memórias" class="w-100" style="object-fit: cover; min-height: 400px; height: 100%;">
                </div>
                <div class="col-md-6 d-flex align-items-center py-5 px-4 px-lg-5">
                    <div>
                        <h2 class="fw-bolder text-dark mb-4 display-5" style="letter-spacing: -0.02em;">Sobre Nós</h2>
                        <p class="text-body-secondary fs-5 mb-4">
                            Todos os dias recebemos perguntas como: <em>Onde posso revelar minhas fotos? Como transformar meus arquivos digitais em impressões de qualidade?</em>
                        </p>
                        <p class="text-body-secondary mb-4">
                            Somos especialistas em transformar suas memórias digitais em impressões de qualidade profissional que você pode guardar para sempre. Cada foto conta uma história, e estamos aqui para garantir que essas histórias sejam preservadas com a qualidade que elas merecem.
                        </p>
                        <p class="text-body-secondary mb-4">
                            Do digital ao impresso, com todo o carinho e dedicação. Utilizamos papel fosco premium e tecnologia de revelação profissional para entregar memórias que duram gerações.
                        </p>

                        <div class="d-flex flex-column flex-md-row gap-4 mt-5">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-check-lg fs-4 fw-bold"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark">Papel Fosco Premium</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-check-lg fs-4 fw-bold"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark">Revelação Profissional</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-check-lg fs-4 fw-bold"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark">Entrega Rápida</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como Funciona Section -->
        <section id="como-funciona" class="py-5 bg-body-tertiary">
            <div class="container-fluid px-4 px-lg-5 py-5">
                <div class="text-center mb-5">
                    <h2 class="fw-bolder text-dark mb-3 display-4" style="letter-spacing: -0.02em;">Como Funciona</h2>
                    <p class="text-body-secondary fs-5">Simples, rápido e seguro</p>
                </div>

                <div class="row g-4">
                    <!-- Passo 1 -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-white rounded-4 shadow-sm p-4 text-center">
                            <div class="display-3 fw-bolder opacity-25 text-primary mb-2">01</div>
                            <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-inline-flex justify-content-center align-items-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
                                <i class="bi bi-box-seam fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Escolha seu pacote</h5>
                            <p class="text-body-secondary mb-0">Selecione o pacote de fotos ou produto que mais combina com você</p>
                        </div>
                    </div>

                    <!-- Passo 2 -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-white rounded-4 shadow-sm p-4 text-center">
                            <div class="display-3 fw-bolder opacity-25 text-primary mb-2">02</div>
                            <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-inline-flex justify-content-center align-items-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
                                <i class="bi bi-cloud-upload fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Envie suas fotos</h5>
                            <p class="text-body-secondary mb-0">Faça upload das suas fotos favoritas de forma rápida e segura</p>
                        </div>
                    </div>

                    <!-- Passo 3 -->
                    <div class="col-md-4">
                        <div class="card h-100 border-0 bg-white rounded-4 shadow-sm p-4 text-center">
                            <div class="display-3 fw-bolder opacity-25 text-primary mb-2">03</div>
                            <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-inline-flex justify-content-center align-items-center mb-4" style="width: 80px; height: 80px; margin: 0 auto;">
                                <i class="bi bi-stars fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-3">Receba suas memórias</h5>
                            <p class="text-body-secondary mb-0">Suas fotos são reveladas com qualidade profissional e entregues</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pacotes de Fotos Section -->
        <section id="pacotes" class="py-5 bg-white">
            <div class="container-fluid px-4 px-lg-5 py-5">
                <div class="text-center mb-5">
                    <h2 class="fw-bolder text-dark mb-3 display-4" style="letter-spacing: -0.02em;">Tire suas memórias do digital</h2>
                    <p class="text-body-secondary fs-5">Escolha o pacote ideal para você</p>
                </div>

                @if($pacotes->isEmpty())
                    <div class="bg-body-tertiary rounded-5 py-5 px-4 text-center border border-light">
                        <div class="py-5 my-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                                <i class="bi bi-image fs-1 text-secondary"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-3">Nenhum pacote disponível</h3>
                            <p class="text-body-secondary fs-5 mb-0 mx-auto" style="max-width: 500px;">
                                Estamos atualizando nossos pacotes. Entre em contato para saber mais!
                            </p>
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($pacotes as $pacote)
                            <div class="col-md-4">
                                <div class="card h-100 border {{ $pacote->name === '40 Fotos' ? 'border-primary shadow' : 'border-secondary-subtle' }} bg-white rounded-4 p-4 text-center product-card-hover d-flex flex-column">
                                    <div style="height: 32px;" class="d-flex align-items-center justify-content-center mb-3">
                                        @if($pacote->name === '40 Fotos')
                                            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                                <i class="bi bi-star-fill me-1"></i> Mais Popular
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="fw-bold text-dark mb-3">{{ $pacote->name }}</h4>

                                    <div class="mb-4">
                                        <span class="display-6 fw-bold text-dark">R$ {{ number_format($pacote->price, 2, ',', '.') }}</span>
                                    </div>

                                    @if($pacote->photo_limit)
                                        <p class="text-body-secondary mb-3">
                                            <i class="bi bi-images me-2"></i> Até {{ $pacote->photo_limit }} fotos
                                        </p>
                                    @endif

                                    @if($pacote->description)
                                        <p class="text-secondary small mb-4">{{ $pacote->description }}</p>
                                    @endif

                                    <div class="mt-auto">
                                        <button type="button"
                                                onclick="addToCart({{ $pacote->id }}, '{{ addslashes($pacote->name) }}', this)"
                                                class="btn btn-dark btn-lg rounded-4 px-4 fw-semibold w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-cart-plus"></i> Eu quero!
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- Outros Produtos Section -->
        @if($outros->isNotEmpty())
            <section class="py-5 bg-body-tertiary">
                <div class="container-fluid px-4 px-lg-5 py-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-bolder text-dark mb-3 display-4" style="letter-spacing: -0.02em;">Produtos Extras</h2>
                        <p class="text-body-secondary fs-5">Complemente seu pedido com esses produtos especiais</p>
                    </div>

                     <div class="row g-4">
                        @foreach($outros as $produto)
                            <div class="col-md-4">
                                <div class="card h-100 border border-secondary-subtle bg-white rounded-4 p-0 text-center product-card-hover overflow-hidden">
                                     @if($produto->image_url)
                                         <div class="position-relative overflow-hidden">
                                             <img src="{{ $produto->image_url }}" alt="{{ $produto->name }}" class="card-img-top transition-all" style="object-fit: cover; height: 200px;">
                                        </div>
                                    @else
                                        <div class="py-4">
                                            <div class="bg-primary-subtle text-primary-emphasis rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 64px; height: 64px; margin: 0 auto;">
                                                <i class="bi bi-gift fs-3"></i>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="p-4">
                                        <h5 class="fw-bold text-dark mb-3">{{ $produto->name }}</h5>

                                        <div class="mb-3">
                                            <span class="fs-4 fw-bold text-dark">R$ {{ number_format($produto->price, 2, ',', '.') }}</span>
                                        </div>

                                        @if($produto->description)
                                            <p class="text-secondary small mb-4">{{ $produto->description }}</p>
                                        @endif

                                        <button type="button"
                                                onclick="addToCart({{ $produto->id }}, '{{ addslashes($produto->name) }}', this)"
                                                class="btn btn-outline-dark btn-lg rounded-4 px-4 fw-semibold w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-cart-plus"></i> Eu quero!
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- FAQ Section -->
        <section id="faq" class="py-5 bg-white">
            <div class="container-fluid px-4 px-lg-5 py-5">
                <div class="text-center mb-5">
                    <h2 class="fw-bolder text-dark mb-3 display-4" style="letter-spacing: -0.02em;">Perguntas Frequentes</h2>
                    <p class="text-body-secondary fs-5">Tire suas dúvidas sobre nosso serviço</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion" id="faqAccordion">
                            <!-- Pergunta 1 -->
                            <div class="accordion-item border-0 rounded-4 mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold text-dark bg-white border-0 rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">
                                        Como funciona o processo?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body bg-body-tertiary text-body-secondary">
                                        É simples! Primeiro, escolha o pacote de fotos que mais combina com você. Depois, faça upload das suas fotos favoritas de forma rápida e segura. Por fim, suas fotos são reveladas com qualidade profissional e entregues para você.
                                    </div>
                                </div>
                            </div>

                            <!-- Pergunta 2 -->
                            <div class="accordion-item border-0 rounded-4 mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold text-dark bg-white border-0 rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                                        Quais formatos de foto são aceitos?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body bg-body-tertiary text-body-secondary">
                                        Aceitamos JPG e PNG, com até 15MB por foto. Garanta o melhor resultado enviando fotos com boa resolução.
                                    </div>
                                </div>
                            </div>

                            <!-- Pergunta 3 -->
                            <div class="accordion-item border-0 rounded-4 mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold text-dark bg-white border-0 rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                                        Quanto tempo leva para receber?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body bg-body-tertiary text-body-secondary">
                                        O prazo varia conforme o produto, mas geralmente em até 7 dias úteis após a confirmação do pagamento.
                                    </div>
                                </div>
                            </div>

                            <!-- Pergunta 4 -->
                            <div class="accordion-item border-0 rounded-4 mb-3 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold text-dark bg-white border-0 rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                                        Como acompanho meu pedido?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body bg-body-tertiary text-body-secondary">
                                        Após criar o pedido, você recebe um link único para acompanhar o status em tempo real. Você também será notificado por WhatsApp a cada etapa do processo.
                                    </div>
                                </div>
                            </div>

                            <!-- Pergunta 5 -->
                            <div class="accordion-item border-0 rounded-4 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-semibold text-dark bg-white border-0 rounded-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                                        Como funciona o pagamento?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body bg-body-tertiary text-body-secondary">
                                        O pagamento é combinado separadamente via WhatsApp ou presencial, de acordo com sua preferência. Aceitamos PIX, cartão de crédito e débito.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <script>
        function landingPage() {
            return {}
        }
    </script>
@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }
        .product-card-hover {
            transition: all 0.3s ease;
        }
        .product-card-hover:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            transform: translateY(-4px);
        }
        .product-card-hover .card-img-top {
            transition: transform 0.3s ease;
        }
        .product-card-hover:hover .card-img-top {
            transform: scale(1.05);
        }
        .hover-white:hover {
            color: #ffffff !important;
        }
        .space-y-3 > * + * {
            margin-top: 1rem;
        }
        .bounce {
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
@endpush
@endsection
