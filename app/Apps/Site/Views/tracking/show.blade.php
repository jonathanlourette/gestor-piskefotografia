@php
    /** @var \App\Domains\Order\Order $order */
@endphp

@extends('site::layouts.site')

@section('title', "Acompanhamento do Pedido #{$order->id} - Piske Memórias")
@section('header_solid', true)

@section('content')
    <div x-data="trackingView()" class="container-fluid px-4 px-lg-5 py-5">

        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="fw-bolder mb-3 text-dark display-4" style="letter-spacing: -0.02em;">Acompanhamento do Pedido</h1>
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
        <div class="bg-white rounded-5 shadow-sm border border-light p-5 mb-5">
            <div class="d-flex justify-content-between align-items-center position-relative">

                <!-- Linha de fundo do stepper -->
                <div class="position-absolute start-0 top-50 translate-middle-y w-100" style="height: 6px; background: linear-gradient(90deg, #1a1a2e 0%, #1a1a2e 100%); z-index: 0; opacity: 0.1;"></div>

                @php
                    $statuses = \App\Domains\Order\Enums\OrderStatusEnum::cases();
                    $currentIndex = array_search($order->status, $statuses);
                @endphp

                @foreach($statuses as $index => $status)
                    @php
                        $isCurrent = $status === $order->status;
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
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $bgClass }} {{ $borderClass }} border border-3 mb-3 {{ $isFuture ? 'opacity-25' : '' }} shadow-sm"
                             style="width: 72px; height: 72px;">

                            @if($isPast || $isCurrent)
                                @if($isCurrent)
                                    <i class="bi bi-hourglass-split fs-3 text-white"></i>
                                @else
                                    <i class="bi bi-check-lg fs-3 text-white"></i>
                                @endif
                            @else
                                <i class="bi bi-circle fs-3 {{ $textClass }}"></i>
                            @endif
                        </div>

                        <!-- Label do status -->
                        <div class="fw-semibold {{ $isFuture ? 'text-secondary' : 'text-dark' }} fs-5">
                            {{ $status->label() }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mensagem contextual baseada no status -->
            <div class="mt-4 pt-4 border-top border-light">
                <div class="rounded-4 p-4 d-flex align-items-center gap-3" :class="'bg-{{ $order->status->color() }}-subtle text-{{ $order->status->color() }}-emphasis'">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white" :class="'bg-{{ $order->status->color() }}'" style="width: 56px; height: 56px;">
                        @switch($order->status)
                            @case(\App\Domains\Order\Enums\OrderStatusEnum::ENVIADO)
                                <i class="bi bi-box-seam fs-4"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::PAGO)
                                <i class="bi bi-check-circle fs-4"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::REVELANDO)
                                <i class="bi bi-gear-wide-connected fs-4"></i>
                            @break

                            @case(\App\Domains\Order\Enums\OrderStatusEnum::CONCLUIDO)
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            @break
                        @endswitch
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 fs-5">Status: {{ $order->status->label() }}</h6>
                        <p class="mb-0 fs-5">
                            @switch($order->status)
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
        <div class="bg-white rounded-5 shadow-sm border border-light p-5 mb-5">
            <h3 class="fw-bold mb-4 text-dark display-5">Produtos do Pedido</h3>

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
                                <th class="ps-4 py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Produto</th>
                                <th class="py-3 text-center text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Quantidade</th>
                                <th class="py-3 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle pe-4" style="font-size: 0.75rem; letter-spacing: 0.05em;">Preço Unitário</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr class="border-bottom border-light">
                                    <td class="ps-4 py-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary-subtle text-primary-emphasis rounded-4 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 48px; height: 48px;">
                                                <i class="bi bi-image fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark fs-5">{{ $item->product->name }}</h6>
                                                <span class="text-secondary small">{{ $item->product->description ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="badge bg-light text-dark border border-secondary-subtle rounded-pill px-3 py-2 fw-medium fs-5">
                                            {{ $item->quantity }}x
                                        </span>
                                    </td>
                                    <td class="py-4 text-end pe-4">
                                        <span class="fw-semibold text-dark fs-5">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="mt-4 pt-4 border-top border-light d-flex justify-content-end">
                    <div class="text-end">
                        <span class="text-body-secondary fs-5 me-3">Total do Pedido</span>
                        <span class="fw-bold fs-4 text-dark">R$ {{ number_format($order->total(), 2, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Contato -->
        <div class="bg-body-tertiary rounded-5 p-5 text-center border border-light">
            <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                <i class="bi bi-whatsapp fs-1 text-success"></i>
            </div>
            <h4 class="fw-bold mb-3 text-dark display-5">Precisa de ajuda?</h4>
            <p class="text-body-secondary fs-5 mb-4">
                Entre em contato conosco pelo WhatsApp caso tenha dúvidas sobre seu pedido.
            </p>
            <a href="https://wa.me/5527997812533" target="_blank" rel="noopener noreferrer"
               class="btn btn-lg rounded-4 px-5 fw-semibold d-inline-flex align-items-center gap-2" style="background-color: #25D366; color: white;">
                <i class="bi bi-whatsapp fs-5"></i>
                Falar no WhatsApp
            </a>
        </div>

    </div>
@endsection