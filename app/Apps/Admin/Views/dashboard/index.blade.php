@php
    /** @var int $novos */
    /** @var int $pagos */
    /** @var int $revelando */
    /** @var int $concluidos */
    /** @var int $totalMes */
    /** @var int $galleriesCount */
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Domains\Order\Order[] $ultimosPedidos */
    /** @var array<string> $chartLabels */
    /** @var array<int> $ordersData */
    /** @var array<float> $revenueData */
@endphp

@extends('admin::layouts.admin')

@section('title', 'Dashboard - Piske Memórias')

@section('content')
    <div x-data="dashboardManager()" class="container-fluid px-4 py-5">
        <!-- Page Header -->
        <div class="mb-5">
            <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.02em;">Dashboard</h1>
            <p class="text-body-secondary mb-0 fs-5">Visão geral do seu negócio e métricas em tempo real.</p>
        </div>

        <!-- Stats Cards Grid -->
        <div class="row g-4 mb-5">
            <!-- Pedidos Novos -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #ffc107; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-warning-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-inbox text-warning fs-5"></i>
                        </div>
                        <span class="text-warning-emphasis fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Novos</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $novos }}</div>
                    <div class="text-body-secondary small">Aguardando pgto</div>
                </div>
            </div>

            <!-- Pagos -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #0d6efd; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-check-circle text-primary fs-5"></i>
                        </div>
                        <span class="text-primary-emphasis fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Pagos</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $pagos }}</div>
                    <div class="text-body-secondary small">Prontos para revelar</div>
                </div>
            </div>

            <!-- Em Revelação -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #0dcaf0; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-info-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-camera text-info fs-5"></i>
                        </div>
                        <span class="text-info-emphasis fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Revelando</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $revelando }}</div>
                    <div class="text-body-secondary small">Em processamento</div>
                </div>
            </div>

            <!-- Concluídos -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #198754; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-trophy text-success fs-5"></i>
                        </div>
                        <span class="text-success-emphasis fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Concluídos</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $concluidos }}</div>
                    <div class="text-body-secondary small">Pedidos finalizados</div>
                </div>
            </div>

            <!-- Total do Mês -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #6f42c1; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-purple-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(111,66,193,0.1);">
                            <i class="bi bi-calendar-check fs-5" style="color: #6f42c1;"></i>
                        </div>
                        <span class="fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em; color: #6f42c1;">Mês Atual</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $totalMes }}</div>
                    <div class="text-body-secondary small">{{ now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}</div>
                </div>
            </div>

            <!-- Galeria Ativa -->
            <div class="col-12 col-md-6 col-lg">
                <div class="bg-white border-0 rounded-4 p-4 h-100 shadow-sm" style="border-top: 3px solid #fd7e14; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-warning-subtle rounded-4 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(253,126,20,0.1);">
                            <i class="bi bi-images fs-5" style="color: #fd7e14;"></i>
                        </div>
                        <span class="fs-7 fw-semibold text-uppercase" style="letter-spacing: 0.05em; color: #fd7e14;">Galerias</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-1">{{ $galleriesCount }}</div>
                    <div class="text-body-secondary small">Galerias ativas</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-5">
            <!-- Gráfico 1: Pedidos por Dia -->
            <div class="col-12 col-lg-6">
                <div class="bg-white rounded-4 border border-secondary-subtle p-4 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-cart3 text-primary fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Pedidos por Dia</h5>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico 2: Faturamento por Dia -->
            <div class="col-12 col-lg-6">
                <div class="bg-white rounded-4 border border-secondary-subtle p-4 shadow-sm h-100">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-success-subtle rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-currency-dollar text-success fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Faturamento por Dia</h5>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Pedidos Section -->
        <div class="bg-white rounded-4 border border-secondary-subtle p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.01em;">Últimos Pedidos</h5>
                @if($ultimosPedidos->count() >= 10)
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light bg-body-tertiary text-dark rounded-3 px-3 py-2 fw-medium shadow-sm hover-primary">
                        Ver todos <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>

            @if($ultimosPedidos->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-5 my-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-body-tertiary rounded-circle mb-4" style="width: 96px; height: 96px;">
                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-3">Nenhum pedido ainda</h2>
                    <p class="text-body-secondary fs-5 mb-0 mx-auto" style="max-width: 500px;">
                        Compartilhe o link da loja para começar a receber pedidos!
                    </p>
                </div>
            @else
                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Nº Pedido</th>
                                <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Cliente</th>
                                <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Telefone</th>
                                <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status</th>
                                <th class="py-3 text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Data</th>
                                <th class="pe-3 py-3 text-end text-secondary fw-semibold text-uppercase border-bottom border-secondary-subtle" style="font-size: 0.75rem; letter-spacing: 0.05em;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ultimosPedidos as $order)
                                <tr class="border-bottom border-light">
                                    <td class="ps-3 py-3">
                                        <span class="font-monospace fw-semibold text-dark fs-6">#{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-dark fw-medium fs-6">{{ $order->customer_name }}</span>
                                    </td>
                                    <td class="py-3">
                                        <a href="tel:{{ $order->customer_phone }}" class="text-secondary text-decoration-none hover-primary">
                                            {{ $order->customer_phone }}
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-{{ $order->status->color() }}-subtle text-{{ $order->status->color() }}-emphasis border border-{{ $order->status->color() }}-subtle rounded-pill px-3 py-2 fw-medium fs-7">
                                            {{ $order->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-body-secondary fs-6">
                                            {{ $order->created_at->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-light bg-body-tertiary text-dark border-0 rounded-3 px-3 py-2" title="Ver detalhes">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($ultimosPedidos->count() >= 10)
                    <div class="text-center mt-4">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-dark rounded-4 px-4 py-2 fw-semibold">
                            Ver todos os pedidos <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <style>
        .hover-primary:hover {
            color: #0d6efd !important;
        }
        .hover-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>

    <script>
        function dashboardManager() {
            return {
                // Placeholder para futuras interações
            }
        }
    </script>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartLabels = @json($chartLabels);
                const ordersData = @json($ordersData);
                const revenueData = @json($revenueData);

                // Gráfico 1: Pedidos por Dia
                new Chart(document.getElementById('ordersChart'), {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: ordersData,
                            backgroundColor: 'rgba(13, 110, 253, 0.8)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barPercentage: 0.7,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return ctx.parsed.y + ' pedido(s)';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                }
                            }
                        }
                    }
                });

                // Gráfico 2: Faturamento por Dia
                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: revenueData,
                            backgroundColor: 'rgba(25, 135, 84, 0.8)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            barPercentage: 0.7,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        return 'R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
