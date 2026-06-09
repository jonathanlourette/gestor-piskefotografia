@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Domains\Order\Order[] $orders */
    /** @var array $filters */
@endphp

@extends('admin::layouts.admin')

@section('title', 'Pedidos - Admin')

@section('content')
<div x-data="orderManager()" class="d-flex flex-column gap-4">
    
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
        <div>
            <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em; font-size: 2rem;">Pedidos</h1>
            <p class="text-body-secondary mb-0">Gerencie os pedidos recebidos e acompanhe o status de revelação.</p>
        </div>
        
        <!-- Search Form -->
        <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2" style="max-width: 400px;">
            @if(isset($filters['status']))
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
            @endif
            <div class="position-relative w-100">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                 <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                        class="form-control form-control-lg bg-body-tertiary border-0 ps-5 rounded-4 shadow-sm" 
                        placeholder="Buscar por nome ou telefone..."
                        style="transition: box-shadow 0.2s ease;"
                        onfocus="this.style.boxShadow='0 0 0 3px rgba(13, 110, 253, 0.15)'" 
                        onblur="this.style.boxShadow='none'">
            </div>
            <button type="submit" class="btn btn-dark btn-lg rounded-4 px-4 fw-semibold shadow-sm">
                <i class="bi bi-funnel"></i>
            </button>
            @if(isset($filters['search']))
                <a href="{{ route('admin.orders.index', isset($filters['status']) ? ['status' => $filters['status']] : []) }}" 
                   class="btn btn-outline-secondary btn-lg rounded-4 px-3 fw-semibold" title="Limpar busca">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Status Tabs -->
    <ul class="nav nav-pills rounded-4 p-1 bg-body-tertiary gap-1" id="pills-tab" role="tablist">
        <li class="nav-item flex-grow-1">
            <a class="nav-link {{ !isset($filters['status']) ? 'active bg-white shadow-sm text-dark' : 'text-body-secondary' }} rounded-3 fw-semibold text-center py-2"
               href="{{ route('admin.orders.index') }}" role="tab">
                Todos
            </a>
        </li>
        @foreach(\App\Domains\Order\Enums\OrderStatusEnum::cases() as $statusCase)
            <li class="nav-item flex-grow-1">
                <a class="nav-link {{ isset($filters['status']) && $filters['status'] == $statusCase->value ? 'active bg-white shadow-sm text-dark' : 'text-body-secondary' }} rounded-3 fw-semibold text-center py-2"
                   href="{{ route('admin.orders.index', ['status' => $statusCase->value]) }}"
                   role="tab">
                    {{ $statusCase->label() }}
                </a>
            </li>
        @endforeach
    </ul>

    @if($orders->isEmpty())
        <!-- Empty State -->
        <div class="bg-body-tertiary rounded-5 py-5 px-4 text-center border border-light">
            <div class="py-5 my-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-4" style="width: 96px; height: 96px;">
                    <i class="bi bi-inbox fs-1 text-secondary"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3">Nenhum pedido encontrado</h2>
                <p class="text-body-secondary fs-5 mb-4 mx-auto" style="max-width: 500px;">
                    Não há pedidos cadastrados com os filtros selecionados no momento.
                </p>
                @if(isset($filters['status']) || isset($filters['search']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-dark btn-lg rounded-4 px-5 fw-semibold shadow-sm">
                        Limpar Filtros
                    </a>
                @endif
            </div>
        </div>
    @else
        <!-- Orders Table -->
        <div class="table-responsive rounded-4 border border-secondary-subtle bg-white shadow-sm">
            <table class="table table-borderless table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Pedido</th>
                        <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Cliente</th>
                        <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Telefone</th>
                        <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status</th>
                        <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Data</th>
                        <th class="pe-4 py-3 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                     @foreach($orders as $order)
                         <tr class="border-bottom border-light" style="transition: background-color 0.2s ease;">
                            <td class="ps-4 py-4">
                                <span class="fw-bold text-dark fs-6">#{{ str_pad((string)$order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                @if($order->uuid)
                                    <span class="text-secondary small font-monospace d-block">UUID: {{ substr($order->uuid, 0, 8) }}...</span>
                                @endif
                            </td>
                            <td class="py-4">
                                <span class="fw-medium text-dark">{{ $order->customer_name }}</span>
                            </td>
                            <td class="py-4">
                                <span class="text-body-secondary fs-6">{{ $order->customer_phone }}</span>
                            </td>
                            <td class="py-4">
                                <span class="badge bg-{{ $order->status->color() }}-subtle text-{{ $order->status->color() }}-emphasis border border-{{ $order->status->color() }}-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="py-4 text-body-secondary fs-6">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                             <td class="pe-4 py-4 text-end">
                                 <a href="{{ route('admin.orders.show', $order->id) }}" 
                                    class="btn btn-sm btn-light bg-body-tertiary text-secondary hover-primary border-0 rounded-3 px-3 py-2 fw-semibold shadow-sm" 
                                    title="Ver Detalhes"
                                    style="transition: all 0.2s ease;">
                                     <i class="bi bi-eye-fill me-1"></i> Detalhes
                                 </a>
                             </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-secondary small">
                    Mostrando de {{ $orders->firstItem() }} a {{ $orders->lastItem() }} de {{ $orders->total() }} registros
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    @endif

</div>
@endsection