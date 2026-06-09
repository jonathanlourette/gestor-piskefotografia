@php
    /** @var \App\Domains\Order\Order $order */
    /** @var string $trackingUrl */
@endphp

@extends('site::layouts.site')

@section('title', 'Pedido Confirmado — Piske Memórias')
@section('header_solid', true)

@section('content')
    <div x-data="confirmationPage()" class="container-fluid px-4 px-lg-5 py-5">
        <div class="col-lg-6 mx-auto">
            <div class="text-center py-5 my-4">
                <!-- Success Icon - Animated -->
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 120px; height: 120px; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%); animation: scaleIn 0.5s ease-out;">
                    <i class="bi bi-check-circle text-white" style="font-size: 4rem;"></i>
                </div>

                <!-- Title -->
                <h1 class="fw-bolder text-dark mb-2 display-4" style="letter-spacing: -0.02em;">Pedido criado com sucesso!</h1>
                <p class="text-body-secondary fs-5 mb-4">Tudo certo. Seu pedido foi recebido e já está sendo processado.</p>

                <!-- Order Number -->
                <div class="bg-body-tertiary rounded-4 p-4 mb-4">
                    <span class="text-body-secondary small fw-semibold text-uppercase d-block mb-1" style="letter-spacing: 0.05em;">Número do Pedido</span>
                    <span class="font-monospace fw-bold text-dark fs-4">#{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>

                <!-- Tracking Link -->
                <div class="bg-white rounded-4 border border-secondary-subtle p-4 mb-4 shadow-sm">
                    <span class="text-body-secondary small fw-semibold text-uppercase d-block mb-2" style="letter-spacing: 0.05em;">Link de Rastreio</span>
                    <div class="input-group input-group-lg">
                        <input
                            type="text"
                            class="form-control bg-body-tertiary border-0 shadow-none rounded-start-4 font-monospace"
                            :value="'{{ $trackingUrl }}'"
                            readonly
                            x-ref="trackingInput"
                        >
                        <button
                            type="button"
                            class="btn rounded-end-4 fw-semibold"
                            :class="copied ? 'btn-success' : 'btn-dark'"
                            @click="copyLink"
                        >
                            <template x-if="!copied">
                                <span><i class="bi bi-clipboard me-1"></i> Copiar</span>
                            </template>
                            <template x-if="copied">
                                <span><i class="bi bi-check-lg me-1"></i> Copiado!</span>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-body-tertiary rounded-4 p-4 mb-5">
                    <p class="text-body-secondary mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Acompanhe o status do seu pedido pelo link de rastreio acima. Você receberá atualizações pelo WhatsApp.
                    </p>
                </div>

                <!-- Back Link -->
                <a href="{{ route('site.landing.index') }}" class="btn btn-dark btn-lg rounded-4 fw-semibold px-5">
                    <i class="bi bi-house me-2"></i>Voltar à Página Inicial
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @keyframes scaleIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function confirmationPage() {
            return {
                copied: false,

                copyLink() {
                    const url = '{{ $trackingUrl }}';

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(() => {
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 2500);
                        });
                    } else {
                        const input = this.$refs.trackingInput;
                        input.select();
                        input.setSelectionRange(0, 99999);
                        document.execCommand('copy');
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2500);
                    }
                },
            };
        }
    </script>
@endpush
