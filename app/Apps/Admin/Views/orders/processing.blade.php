@php
    /** @var \App\Domains\Order\Order $order */

    $isProcessing = $order->status === \App\Domains\Order\Enums\OrderStatusEnum::PROCESSANDO;
    $totalPhotos = $order->items->flatMap->photos->count();
    $processedPhotos = $order->items->flatMap->photos->filter(fn ($p) => $p->original_s3_path === null)->count();

    $title = $isProcessing ? 'Processando Fotos' : 'Aguardando Envio';
    $description = $isProcessing
        ? 'As fotos estão sendo processadas em segundo plano.'
        : 'O cliente ainda não finalizou o envio das fotos.';
@endphp

@extends('admin::layouts.admin')

@section('title', "Pedido #{$order->id} - {$title} - Admin")

@section('content')
    @if($isProcessing)
        <meta http-equiv="refresh" content="10">
    @endif

    <div class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
        <div class="text-center" style="max-width: 480px;">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4 {{ $isProcessing ? 'bg-secondary-subtle' : 'bg-warning-subtle' }}" style="width: 96px; height: 96px;">
                @if($isProcessing)
                    <div class="spinner-border text-secondary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Processando...</span>
                    </div>
                @else
                    <i class="bi bi-clock-history text-warning" style="font-size: 2.5rem;"></i>
                @endif
            </div>

            <h1 class="fw-bolder text-dark mb-2" style="font-size: 1.75rem;">{{ $title }}</h1>
            <p class="text-body-secondary mb-4">{{ $description }}</p>

            <p class="text-body-secondary fs-5 mb-1">
                Pedido <span class="font-monospace bg-body-tertiary px-3 py-2 rounded-4 fw-bold text-dark">#{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span>
            </p>

            <p class="text-body-secondary small mb-4">
                Recebido em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
            </p>

            @if($isProcessing && $totalPhotos > 0)
                <div class="bg-white rounded-4 border border-secondary-subtle p-4 shadow-sm mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-dark">Progresso</span>
                        <span class="text-body-secondary fw-medium">{{ $processedPhotos }} de {{ $totalPhotos }} fotos processadas</span>
                    </div>
                    <div class="progress bg-body-tertiary rounded-pill" style="height: 10px;">
                        @php $percent = $totalPhotos > 0 ? round(($processedPhotos / $totalPhotos) * 100) : 0 @endphp
                        <div class="progress-bar rounded-pill" role="progressbar"
                             style="width: {{ $percent }}%; background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);"
                             aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <p class="text-secondary small mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Esta página é atualizada automaticamente a cada 10 segundos.
                </p>
            @endif

            @if(! $isProcessing)
                <div class="bg-white rounded-4 border border-warning-subtle p-4 shadow-sm mb-4">
                    <p class="mb-0 text-secondary">
                        <i class="bi bi-person me-1"></i>
                        <strong>{{ $order->customer_name }}</strong>
                    </p>
                    <p class="mb-0 text-secondary">
                        <i class="bi bi-phone me-1"></i>
                        {{ $order->customer_phone }}
                    </p>
                </div>
            @endif

            <a href="{{ route('admin.orders.index') }}"
               class="btn btn-outline-dark rounded-4 px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Voltar para Lista
            </a>
        </div>
    </div>
@endsection
