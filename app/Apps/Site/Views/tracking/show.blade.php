@php
    /** @var \App\Domains\Order\Order $order */
@endphp

@extends('site::layouts.site')

@section('title', "Acompanhamento do Pedido #{$order->id} - Piske Memórias")
@section('header_solid', true)

@section('content')
    <div x-data="trackingView()" class="container-fluid px-4 px-lg-5 py-5">

        <!-- Page Header -->
        <div class="text-center mb-4 mb-lg-5">
            <h1 class="fw-bolder mb-3 text-dark" style="letter-spacing: -0.02em; font-size: 1.75rem;">
                <span class="d-none d-md-inline display-4">Acompanhamento do Pedido</span>
                <span class="d-inline d-md-none">Acompanhamento do Pedido</span>
            </h1>
            <div class="d-inline-flex align-items-center gap-3 text-body-secondary">
                <span class="d-inline-flex align-items-center gap-2">
                    <i class="bi bi-hash"></i>
                    <span class="fw-semibold">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                </span>
                <span class="text-secondary">•</span>
                <span class="d-inline-flex align-items-center gap-2">
                    <i class="bi bi-calendar3"></i>
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>

        <!-- Status Pipeline Stepper -->
        <div class="bg-white rounded-4 shadow-sm border border-light p-3 p-lg-5 mb-4 mb-lg-5">
            <div class="d-flex justify-content-between align-items-center position-relative">

                <!-- Linha de fundo do stepper -->
                <div class="position-absolute start-0 top-50 translate-middle-y w-100" style="height: 4px; background: #1a1a2e; z-index: 0; opacity: 0.1;"></div>

                @php
                    // Status visíveis ao cliente (PROCESSANDO/PROCESSADO são internos)
                    $statuses = [
                        \App\Domains\Order\Enums\OrderStatusEnum::ENVIADO,
                        \App\Domains\Order\Enums\OrderStatusEnum::PAGO,
                        \App\Domains\Order\Enums\OrderStatusEnum::REVELANDO,
                        \App\Domains\Order\Enums\OrderStatusEnum::CONCLUIDO,
                    ];

                    // Mapeia status interno para o status visível correspondente
                    $displayStatus = match($order->status) {
                        \App\Domains\Order\Enums\OrderStatusEnum::PROCESSANDO => \App\Domains\Order\Enums\OrderStatusEnum::ENVIADO,
                        \App\Domains\Order\Enums\OrderStatusEnum::PROCESSADO => \App\Domains\Order\Enums\OrderStatusEnum::ENVIADO,
                        default => $order->status,
                    };
                    $currentIndex = array_search($displayStatus, $statuses);
                @endphp

                @foreach($statuses as $index => $status)
                    @php
                        $isCurrent = $status === $displayStatus;
                        $isPast = $index < $currentIndex;
                        $isFuture = $index > $currentIndex;

                        $colorClass = match($status) {
                            \App\Domains\Order\Enums\OrderStatusEnum::ENVIADO => 'warning',
                            \App\Domains\Order\Enums\OrderStatusEnum::PAGO => 'primary',
                            \App\Domains\Order\Enums\OrderStatusEnum::REVELANDO => 'info',
                            \App\Domains\Order\Enums\OrderStatusEnum::CONCLUIDO => 'success',
                        };

                        $bgClass = match(true) {
                            $isPast || $isCurrent => "bg-{$colorClass}",
                            default => 'bg-white',
                        };

                        $textClass = match(true) {
                            $isPast || $isCurrent => "text-{$colorClass}",
                            default => 'text-secondary',
                        };

                        $borderClass = match(true) {
                            $isPast || $isCurrent => "border-{$colorClass}",
                            default => 'border-secondary-subtle',
                        };
                    @endphp

                    <div class="position-relative z-1 text-center" style="flex: 1;">
                        <!-- Círculo do status -->
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $bgClass }} {{ $borderClass }} border border-3 mb-2 mb-lg-3 {{ $isFuture ? 'opacity-25' : '' }} shadow-sm"
                             style="width: 44px; height: 44px;">

                            @if($isPast || $isCurrent)
                                @if($isCurrent)
                                    <i class="bi bi-hourglass-split text-white" style="font-size: 1rem;"></i>
                                @else
                                    <i class="bi bi-check-lg text-white" style="font-size: 1.2rem;"></i>
                                @endif
                            @else
                                <i class="bi bi-circle {{ $textClass }}" style="font-size: 0.8rem;"></i>
                            @endif
                        </div>

                        <!-- Label do status -->
                        <div class="fw-semibold {{ $isFuture ? 'text-secondary' : 'text-dark' }}" style="font-size: 0.7rem; line-height: 1.1;">
                            {{ $status->label() }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mensagem contextual baseada no status -->
            <div class="mt-3 mt-lg-4 pt-3 pt-lg-4 border-top border-light">
                <div class="rounded-4 p-3 p-lg-4 d-flex align-items-center gap-2 gap-lg-3" :class="'bg-{{ $displayStatus->color() }}-subtle text-{{ $displayStatus->color() }}-emphasis'">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white" :class="'bg-{{ $displayStatus->color() }}'" style="width: 44px; height: 44px;">
                        @switch($displayStatus)
                            @case(\App\Domains\Order\Enums\OrderStatusEnum::ENVIADO)
                                <i class="bi bi-box-seam fs-5"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::PAGO)
                                <i class="bi bi-check-circle fs-5"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::REVELANDO)
                                <i class="bi bi-gear-wide-connected fs-5"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::CONCLUIDO)
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            @break
                        @endswitch
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1" style="font-size: 0.95rem;">Status: {{ $displayStatus->label() }}</h6>
                        <p class="mb-0" style="font-size: 0.85rem;">
                            @switch($displayStatus)
                                @case(\App\Domains\Order\Enums\OrderStatusEnum::ENVIADO)
                                    Seu pedido foi recebido e aguarda confirmação de pagamento.
                                @break

                                @case(\App\Domains\Order\Enums\OrderStatusEnum::PAGO)
                                    Pagamento confirmado! Seu pedido será iniciado em breve.
                                @break

                                @case(\App\Domains\Order\Enums\OrderStatusEnum::REVELANDO)
                                    Suas fotos estão sendo reveladas! Em breve estarão prontas.
                                @break

                                @case(\App\Domains\Order\Enums\OrderStatusEnum::CONCLUIDO)
                                    Seu pedido está pronto! Entre em contato para combinar a entrega.
                                @break
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="bg-white rounded-4 shadow-sm border border-light p-3 p-lg-5 mb-4 mb-lg-5">
            <h3 class="fw-bold mb-3 mb-lg-4 text-dark" style="font-size: 1.15rem;">Produtos do Pedido</h3>

            @if($order->items->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                    <p>Nenhum produto encontrado neste pedido.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr class="bg-light">
                                <th class="ps-3 py-2 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.65rem; letter-spacing: 0.05em;">Produto</th>
                                <th class="py-2 text-center text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.65rem; letter-spacing: 0.05em; width: 60px;">Qtd</th>
                                <th class="py-2 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle pe-3" style="font-size: 0.65rem; letter-spacing: 0.05em; white-space: nowrap;">Preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr class="border-bottom border-light">
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary-subtle text-primary-emphasis rounded-3 d-none d-md-flex justify-content-center align-items-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                <i class="bi bi-image fs-6"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $item->product->name }}</div>
                                                <span class="text-secondary" style="font-size: 0.75rem;">{{ $item->product->description ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-light text-dark border border-secondary-subtle rounded-pill px-2 py-1 fw-medium" style="font-size: 0.8rem;">
                                            {{ $item->quantity }}x
                                        </span>
                                    </td>
                                    <td class="py-3 text-end pe-3">
                                        <span class="fw-semibold text-dark" style="font-size: 0.9rem;">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="mt-3 pt-3 border-top border-light d-flex justify-content-end">
                    <div class="text-end">
                        <span class="text-body-secondary me-2" style="font-size: 0.85rem;">Total</span>
                        <span class="fw-bold fs-5 text-dark">R$ {{ number_format($order->total(), 2, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Contato -->
        <div class="bg-body-tertiary rounded-4 p-4 p-lg-5 text-center border border-light">
            <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-whatsapp fs-2 text-success"></i>
            </div>
            <h4 class="fw-bold mb-2 text-dark" style="font-size: 1.15rem;">Precisa de ajuda?</h4>
            <p class="text-body-secondary mb-3" style="font-size: 0.9rem;">
                Entre em contato conosco pelo WhatsApp caso tenha dúvidas sobre seu pedido.
            </p>
            <a href="https://wa.me/5527997812533" target="_blank" rel="noopener noreferrer"
               class="btn rounded-4 px-4 fw-semibold d-inline-flex align-items-center gap-2" style="background-color: #25D366; color: white;">
                <i class="bi bi-whatsapp fs-5"></i>
                Falar no WhatsApp
            </a>
        </div>

    </div>
@endsection