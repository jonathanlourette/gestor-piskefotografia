<?php

declare(strict_types=1);

namespace App\Domains\Order\Actions;

use App\Domains\Gallery\Gallery;
use App\Domains\Order\Enums\OrderStatusEnum;
use App\Domains\Order\Order;
use App\Support\Action;
use App\Support\Exceptions\BusinessException;

final class RetrieveDashboardDataAction extends Action
{
    /**
     * @throws BusinessException
     */
    public function perform(): mixed
    {
        try {
            $statusCounts = Order::query()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $novos = $statusCounts[OrderStatusEnum::ENVIADO->value] ?? 0;
            $pagos = $statusCounts[OrderStatusEnum::PAGO->value] ?? 0;
            $revelando = $statusCounts[OrderStatusEnum::REVELANDO->value] ?? 0;
            $concluidos = $statusCounts[OrderStatusEnum::CONCLUIDO->value] ?? 0;

            $totalMes = Order::query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $ultimosPedidos = Order::query()
                ->with('items')
                ->latest()
                ->take(10)
                ->get();

            $thirtyDaysAgo = now()->subDays(30)->startOfDay();

            $chartData = Order::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupByRaw('DATE(created_at)')
                ->pluck('order_count', 'date')
                ->toArray();

            $revenueData = Order::query()
                ->with('items')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->get()
                ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'));

            $chartLabels = [];
            $ordersChartData = [];
            $revenueChartData = [];

            for ($i = 29; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $dateKey = $day->format('Y-m-d');

                $chartLabels[] = $day->format('d/m');
                $ordersChartData[] = $chartData[$dateKey] ?? 0;
                $revenueChartData[] = (float) ($revenueData->get($dateKey)?->sum(fn (Order $order) => $order->total()) ?? 0);
            }

            $galleriesCount = Gallery::count();

            return [
                'novos' => $novos,
                'pagos' => $pagos,
                'revelando' => $revelando,
                'concluidos' => $concluidos,
                'totalMes' => $totalMes,
                'galleriesCount' => $galleriesCount,
                'ultimosPedidos' => $ultimosPedidos,
                'chartLabels' => $chartLabels,
                'ordersData' => $ordersChartData,
                'revenueData' => $revenueChartData,
            ];
        } catch (\Throwable $e) {
            report($e);
            throw new BusinessException('Não foi possível carregar os dados do dashboard. Tente novamente.');
        }
    }
}
