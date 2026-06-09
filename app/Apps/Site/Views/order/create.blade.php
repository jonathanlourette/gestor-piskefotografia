@php
    /** @var array<int, array{product_id: int, name: string, price: float, photo_limit: int, quantity: int}> $cartItems */
    /** @var float $total */
@endphp

@extends('site::layouts.site')

@section('title', 'Finalizar Pedido — Piske Memórias')
@section('header_solid', true)

@section('content')
    <div class="container-fluid px-4 px-lg-5 py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 mx-auto">
                <!-- Page Header -->
                <div class="text-center mb-5">
                    <h1 class="fw-bolder mb-2 text-dark display-4" style="letter-spacing: -0.02em;">Finalizar Pedido</h1>
                    <p class="text-body-secondary mb-0 fs-5">Confirme os itens e preencha seus dados para prosseguir.</p>
                </div>

                <!-- Cart Summary - Cards -->
                <div class="mb-5">
                    <div class="d-flex gap-3 flex-column">
                        @foreach($cartItems as $item)
                            <div class="bg-body-tertiary rounded-4 p-4 border border-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-dark mb-2 fs-5">{{ $item['name'] }}</h6>
                                        <div class="d-flex align-items-center gap-4 text-body-secondary">
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="bi bi-hash"></i> {{ $item['quantity'] }}x
                                            </span>
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="bi bi-currency-dollar"></i> R$ {{ number_format($item['price'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-dark fs-5">R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Total Card -->
                    <div class="bg-dark text-white rounded-4 p-4 mt-4 shadow-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-white-50">Total do Pedido</span>
                            </div>
                            <div>
                                <span class="fw-bold fs-3">R$ {{ number_format($total, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Form -->
                <div class="bg-white rounded-4 border border-secondary-subtle p-4 p-md-5 shadow-sm">
                    <h4 class="fw-bold text-dark mb-1">Seus Dados</h4>
                    <p class="text-body-secondary mb-4">Precisamos do seu nome e telefone para contato.</p>

                    <form action="{{ route('site.order.store') }}" method="POST" novalidate>
                        @csrf

                        <!-- Nome Completo -->
                        <div class="mb-4">
                            <label for="customer_name" class="form-label fw-semibold text-dark">Nome completo <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="customer_name"
                                id="customer_name"
                                value="{{ old('customer_name') }}"
                                class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 {{ $errors->has('customer_name') ? 'is-invalid' : '' }}"
                                placeholder="Seu nome completo"
                                required
                                autocomplete="name"
                            >
                            @error('customer_name')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Telefone/WhatsApp -->
                        <div class="mb-4">
                            <label for="customer_phone" class="form-label fw-semibold text-dark">Telefone / WhatsApp <span class="text-danger">*</span></label>
                            <input
                                type="tel"
                                name="customer_phone"
                                id="customer_phone"
                                value="{{ old('customer_phone') }}"
                                class="form-control form-control-lg bg-body-tertiary border-0 shadow-none rounded-4 {{ $errors->has('customer_phone') ? 'is-invalid' : '' }}"
                                placeholder="(00) 00000-0000"
                                required
                                autocomplete="tel"
                                x-data="phoneMask()"
                                x-init="init()"
                                @input="applyMask($event)"
                            >
                            @error('customer_phone')
                                <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Actions -->
                        <div class="d-flex flex-column flex-md-row gap-3 mt-4">
                            <a href="{{ route('site.landing.index') }}" class="btn btn-light btn-lg rounded-4 fw-semibold text-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Voltar aos Produtos
                            </a>
                            <button type="submit" class="btn btn-dark btn-lg rounded-4 fw-semibold px-5 flex-grow-1">
                                <i class="bi bi-bag-check me-2"></i>Criar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function phoneMask() {
            return {
                init() {
                    // Apply mask to existing value (e.g. old input)
                    const input = document.getElementById('customer_phone');
                    if (input && input.value) {
                        input.value = this.formatPhone(input.value);
                    }
                },

                applyMask(event) {
                    const input = event.target;
                    input.value = this.formatPhone(input.value);
                },

                formatPhone(value) {
                    // Remove everything that's not a digit
                    let digits = value.replace(/\D/g, '');

                    // Limit to 11 digits (2 DDD + 9 number)
                    digits = digits.substring(0, 11);

                    // Apply mask progressively
                    if (digits.length === 0) return '';
                    if (digits.length <= 2) return `(${digits}`;
                    if (digits.length <= 7) return `(${digits.substring(0, 2)}) ${digits.substring(2)}`;
                    return `(${digits.substring(0, 2)}) ${digits.substring(2, 7)}-${digits.substring(7)}`;
                }
            }
        }
    </script>
@endsection
