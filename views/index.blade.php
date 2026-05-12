@extends('commerced3::app')

@section('content')
    <div class="dashboard-grid">
        {{-- Фильтры периода --}}
        <div class="row">
            <div class="col-12">
                <div class="btn-group" id="period-filter">
                    <button class="btn active" data-period="7d">7 дней</button>
                    <button class="btn" data-period="30d">30 дней</button>
                    <button class="btn" data-period="90d">90 дней</button>
                    <button class="btn" data-period="year">Год</button>
                </div>
            </div>
        </div>

        {{-- График выручки + метрики --}}
        <div class="row">
            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <span>Выручка <small>за период</small></span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-revenue" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="metric-grid">
                    <div class="metric-card">
                        <h6>Выручка сегодня</h6>
                        <div class="value" id="metric-today">0 ₽</div>
                        <small id="metric-vs-yesterday">vs вчера: 0%</small>
                    </div>
                    <div class="metric-card">
                        <h6>Заказов сегодня</h6>
                        <div class="value" id="metric-orders">0</div>
                        <small id="metric-vs-orders">vs вчера: 0%</small>
                    </div>
                    <div class="metric-card">
                        <h6>Средний чек</h6>
                        <div class="value" id="metric-avg">0 ₽</div>
                        <small id="metric-vs-avg">vs вчера: 0%</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Тепловая карта и воронка --}}
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Тепловая карта продаж <small>по дням и часам</small></span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-heatmap" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Воронка заказов</span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-funnel" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Treemap и топ товаров --}}
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Категории товаров <small>по продажам</small></span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-treemap" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Топ-10 товаров <small>по количеству продаж</small></span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-top-products" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sankey и Circle Packing --}}
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Sankey: Пути статусов</span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-sankey" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <span>Пузырьковая диаграмма категорий</span>
                    </div>
                    <div class="card-body chart-container">
                        <div id="chart-circlepack" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('buttons')
    <button class="btn" onclick="location.reload();">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M17.65 6.35A7.96 7.96 0 0012 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0112 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" />
        </svg>
        Обновить
    </button>
    <button class="btn" id="btn-fullscreen">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3" />
        </svg>
        На весь экран
    </button>
@endsection

@push('scripts')
    <script src="{{ MODX_BASE_URL }}assets/modules/commerced3/d3.v7.min.js"></script>
    <script src="{{ MODX_BASE_URL }}assets/modules/commerced3/d3-sankey.min.js"></script>
    <script src="{{ MODX_BASE_URL }}assets/modules/commerced3/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('#period-filter button');
            let currentPeriod = '30d';

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentPeriod = this.dataset.period;
                    loadAllCharts(currentPeriod);
                });
            });

            function loadAllCharts(period) {
                const baseUrl = window.location.href.replace(/\?.*$/, '').replace(/\/$/, '') + '/api';
                const targets = [{
                        url: `${baseUrl}/revenue?period=${period}`,
                        selector: '#chart-revenue',
                        draw: drawRevenueChart
                    },
                    {
                        url: `${baseUrl}/sankey?period=${period}`,
                        selector: '#chart-sankey',
                        draw: drawSankey
                    },
                    {
                        url: `${baseUrl}/heatmap?period=${period}`,
                        selector: '#chart-heatmap',
                        draw: drawHeatmap
                    },
                    {
                        url: `${baseUrl}/funnel?period=${period}`,
                        selector: '#chart-funnel',
                        draw: drawFunnel
                    },
                    {
                        url: `${baseUrl}/treemap?period=${period}`,
                        selector: '#chart-treemap',
                        draw: drawTreemap
                    },
                    {
                        url: `${baseUrl}/treemap?period=${period}`,
                        selector: '#chart-circlepack',
                        draw: drawCirclePacking
                    },
                    {
                        url: `${baseUrl}/products?period=${period}`,
                        selector: '#chart-top-products',
                        draw: drawTopProducts
                    }
                ];

                targets.forEach(({
                    url,
                    selector,
                    draw
                }) => {
                    if (typeof showSpinner === 'function') showSpinner(selector);

                    fetch(url)
                        .then(r => {
                            if (!r.ok) throw new Error('HTTP ' + r.status);
                            return r.json();
                        })
                        .then(data => {
                            if (typeof draw === 'function') draw(selector, data);
                        })
                        .catch(err => {
                            console.warn(selector, err);
                            const el = document.querySelector(selector);
                            if (el) el.innerHTML = '<div class="empty-container">Ошибка загрузки</div>';
                        });
                });
                showSpinnerMetrics();
                fetch(`${baseUrl}/metrics`)
                    .then(r => r.json())
                    .then(data => updateMetrics(data))
                    .catch(err => {
                        console.warn('metrics:', err);
                        document.getElementById('metric-today').textContent = '—';
                        document.getElementById('metric-orders').textContent = '—';
                        document.getElementById('metric-avg').textContent = '—';
                    });
            }
            function showSpinnerMetrics() {
                document.getElementById('metric-today').textContent = '...';
                document.getElementById('metric-orders').textContent = '...';
                document.getElementById('metric-avg').textContent = '...';
            }

            function updateMetrics(data) {
                document.getElementById('metric-today').textContent = data.today + ' ₽';
                document.getElementById('metric-vs-yesterday').textContent =
                    'vs вчера: ' + (data.vsYesterday >= 0 ? '+' : '') + data.vsYesterday + '%';
                document.getElementById('metric-orders').textContent = data.orders;
                document.getElementById('metric-vs-orders').textContent =
                    'vs вчера: ' + (data.ordersVsYesterday >= 0 ? '+' : '') + data.ordersVsYesterday + '%';
                document.getElementById('metric-avg').textContent = data.avgCheck + ' ₽';
                document.getElementById('metric-vs-avg').textContent =
                    'vs вчера: ' + (data.avgVsYesterday >= 0 ? '+' : '') + data.avgVsYesterday + '%';
            }

            document.getElementById('btn-fullscreen').addEventListener('click', function() {
                const el = document.documentElement;
                if (el.requestFullscreen) el.requestFullscreen();
            });

            loadAllCharts(currentPeriod);
        });
    </script>
@endpush
