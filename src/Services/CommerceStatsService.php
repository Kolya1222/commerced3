<?php

namespace roilafx\Commerced3\Services;

use EvolutionCMS\Models\SiteContent;
use roilafx\Commerced3\Models\Order;
use roilafx\Commerced3\Models\OrderProduct;
use roilafx\Commerced3\Models\OrderStatus;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class CommerceStatsService
{
    /**
     * Выручка по дням за период.
     */
    public function getRevenueByDays(int $days): Collection
    {
        return Order::reportable()
            ->period($days)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date'  => $row->date,
                'total' => (float) $row->total,
            ]);
    }

    /**
     * Тепловая карта: день недели × час.
     */
    public function getHeatmapData(int $days): Collection
    {
        return Order::reportable()
            ->period($days)
            ->selectRaw('DAYOFWEEK(created_at) - 1 as day, HOUR(created_at) as hour, COUNT(*) as value')
            ->groupBy('day', 'hour')
            ->orderBy('day')
            ->orderBy('hour')
            ->get()
            ->map(fn($row) => [
                'day'   => (int) $row->day,
                'hour'  => (int) $row->hour,
                'value' => (int) $row->value,
            ]);
    }

    /**
     * Воронка статусов заказов.
     */
    public function getFunnelData(int $days): Collection
    {
        return OrderStatus::withCount(['orders' => function ($q) use ($days) {
            $q->period($days);
        }])
            ->orderBy('id')
            ->get()
            ->map(fn($row) => [
                'status' => $row->title,
                'count'  => (int) $row->orders_count,
            ]);
    }

    /**
     * Treemap: данные по категориям (через отдельный запрос parent).
     */
    public function getTreemapData(int $days): array
    {
        // 1. Получаем все заказы с проданными товарами за период
        $products = OrderProduct::whereHas('order', function ($q) use ($days) {
            $q->reportable()->period($days);
        })
            ->selectRaw(
                'product_id,
                 SUM(count * price) as total'
            )
            ->groupBy('product_id')
            ->get();

        // 2. Собираем все product_id и загружаем их родителей одним запросом
        $productIds = $products->pluck('product_id')->all();
        $parents = SiteContent::whereIn('id', $productIds)
            ->pluck('parent', 'id')
            ->all();

        // 3. Загружаем названия категорий (родителей)
        $categoryIds = array_values(array_unique($parents));
        $categories = SiteContent::whereIn('id', $categoryIds)
            ->pluck('pagetitle', 'id')
            ->all();

        // 4. Агрегируем суммы по категориям
        $data = [];
        foreach ($products as $row) {
            $parentId = $parents[$row->product_id] ?? 0;
            $catName = $categories[$parentId] ?? 'Без категории';
            $key = $parentId . '|' . $catName;
            if (!isset($data[$key])) {
                $data[$key] = [
                    'name'  => $catName,
                    'value' => 0,
                ];
            }
            $data[$key]['value'] += (float) $row->total;
        }

        $children = array_values($data);
        return ['name' => 'Категории', 'children' => $children];
    }

    /**
     * Топ-10 товаров по количеству продаж (используем title из заказа).
     */
    public function getTopProducts(int $days): Collection
    {
        return OrderProduct::whereHas('order', function ($q) use ($days) {
            $q->reportable()->period($days);
        })
            ->selectRaw(
                'title as name,
                 SUM(count) as quantity,
                 SUM(count * price) as total'
            )
            ->groupBy('title')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'name'     => $row->name ?? 'Без названия',
                'quantity' => (int) $row->quantity,
                'total'    => (float) $row->total,
            ]);
    }

    /**
     * Данные для Sankey-диаграммы переходов статусов заказов
     * @param int $days
     * @return array ['nodes' => [...], 'links' => [...]]
     */
    public function getSankeyData(int $days): array
    {
        // Все заказы за период с их историей
        $orders = Order::period($days)
            ->with(['history' => function ($q) {
                $q->orderBy('created_at');
            }])
            ->get();

        $nodes = []; // уникальные статусы
        $links = []; // переходы
        $nodeMap = [];

        foreach ($orders as $order) {
            $prev = null;
            foreach ($order->history as $entry) {
                $statusName = $entry->status->title ?? 'Статус #' . $entry->status_id;
                if (!isset($nodeMap[$statusName])) {
                    $nodeMap[$statusName] = count($nodes);
                    $nodes[] = ['name' => $statusName];
                }
                $curr = $nodeMap[$statusName];
                if ($prev !== null && $prev !== $curr) {
                    // Ищем существующую связь или создаём
                    $found = false;
                    foreach ($links as &$link) {
                        if ($link['source'] === $prev && $link['target'] === $curr) {
                            $link['value']++;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $links[] = ['source' => $prev, 'target' => $curr, 'value' => 1];
                    }
                }
                $prev = $curr;
            }
        }

        return ['nodes' => $nodes, 'links' => $links];
    }

    /**
     * Ключевые метрики (сегодня vs вчера).
     */
    public function getMetrics(): array
    {
        $today     = $this->getMetricsForDate(Carbon::now());
        $yesterday = $this->getMetricsForDate(Carbon::now()->subDay());

        $avgToday     = $today['orders'] > 0     ? round($today['revenue'] / $today['orders'], 2) : 0;
        $avgYesterday = $yesterday['orders'] > 0 ? round($yesterday['revenue'] / $yesterday['orders'], 2) : 0;

        return [
            'today'             => number_format($today['revenue'], 0, '.', ' '),
            'vsYesterday'       => $this->percentDiff($today['revenue'], $yesterday['revenue']),
            'orders'            => $today['orders'],
            'ordersVsYesterday' => $this->percentDiff($today['orders'], $yesterday['orders']),
            'avgCheck'          => number_format($avgToday, 0, '.', ' '),
            'avgVsYesterday'    => $this->percentDiff($avgToday, $avgYesterday),
        ];
    }

    /**
     * Парсинг периода из строки в дни.
     */
    public function parsePeriod(string $period): int
    {
        return match ($period) {
            '7d'   => 7,
            '90d'  => 90,
            'year' => 365,
            default => 30,
        };
    }

    /* ─── Приватные хелперы ──────────────────── */

    private function getMetricsForDate($date): array
    {
        return [
            'revenue' => Order::reportable()
                ->whereDate('created_at', $date)
                ->sum('amount'),
            'orders'  => Order::reportable()
                ->whereDate('created_at', $date)
                ->count(),
        ];
    }

    private function percentDiff(float $current, float $previous): int
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (int) round(($current - $previous) / $previous * 100);
    }
}
