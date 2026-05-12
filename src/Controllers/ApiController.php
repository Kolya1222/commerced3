<?php

namespace roilafx\Commerced3\Controllers;

use roilafx\Commerced3\Services\CommerceStatsService;

class ApiController
{
    protected CommerceStatsService $stats;

    public function __construct()
    {
        $this->stats = new CommerceStatsService();
    }

    public function revenue()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getRevenueByDays($days)
        );
    }

    public function heatmap()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getHeatmapData($days)
        );
    }

    public function funnel()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getFunnelData($days)
        );
    }

    public function treemap()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getTreemapData($days)
        );
    }

    public function products()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getTopProducts($days)
        );
    }

    public function sankey()
    {
        $days = $this->stats->parsePeriod(request()->get('period', '30d'));
        return response()->json(
            $this->stats->getSankeyData($days)
        );
    }

    public function metrics()
    {
        return response()->json(
            $this->stats->getMetrics()
        );
    }
}
