<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commerce Дашборд</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --surface: #161b22;
            --surface-hover: #1c2129;
            --border: #30363d;
            --text: #e6edf3;
            --text-secondary: #8b949e;
            --accent: #4c8bf5;
            --accent-glow: rgba(76, 139, 245, 0.3);
            --radius: 14px;
            --shadow-card: 0 0 0 1px rgba(255, 255, 255, 0.04), 0 4px 12px rgba(0, 0, 0, 0.3);
            --shadow-elevated: 0 0 0 1px rgba(255, 255, 255, 0.06), 0 12px 28px rgba(0, 0, 0, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            padding: 28px 32px;
            -webkit-font-smoothing: antialiased;
        }

        .dashboard-wrap {
            max-width: 1440px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dashboard-header h1 {
            font-size: 1.45rem;
            font-weight: 600;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.3px;
        }

        .dashboard-header h1 .icon {
            width: 30px;
            height: 30px;
            color: var(--accent);
            filter: drop-shadow(0 0 8px var(--accent-glow));
        }

        .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .btn:hover {
            background: var(--surface-hover);
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(76, 139, 245, 0.15);
        }

        .btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 16px var(--accent-glow);
        }

        .btn .icon {
            width: 16px;
            height: 16px;
        }

        .section-body {
            background: transparent;
        }

        .dashboard-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .col-8 {
            flex: 0 0 calc(66.666% - 16px);
            max-width: calc(66.666% - 16px);
        }

        .col-4 {
            flex: 0 0 calc(33.333% - 16px);
            max-width: calc(33.333% - 16px);
        }

        .col-6 {
            flex: 0 0 calc(50% - 12px);
            max-width: calc(50% - 12px);
        }

        @media (max-width: 992px) {

            .col-8,
            .col-4,
            .col-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            display: flex;
            flex-direction: column;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            border-color: var(--accent);
            box-shadow: var(--shadow-elevated), 0 0 20px rgba(76, 139, 245, 0.08);
        }

        .card-header {
            padding: 16px 24px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text);
        }

        .card-header small {
            font-weight: 400;
            color: var(--text-secondary);
            margin-left: 8px;
            font-size: 0.8rem;
        }

        .card-body {
            padding: 20px 24px;
            flex: 1;
            background: var(--surface);
        }

        .metric-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .metric-card {
            padding: 20px 24px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, var(--surface) 0%, #1c2535 100%);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent-glow);
        }

        .metric-card h6 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .metric-card .value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #fff;
            line-height: 1.1;
        }

        .metric-card small {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: block;
        }

        .chart-container {
            width: 100%;
        }

        .chart-container svg {
            display: block;
        }

        .empty-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            font-size: 0.9rem;
            padding: 40px;
            text-align: center;
            opacity: 0.8;
        }

        .d3-tooltip {
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            line-height: 1.4;
            background: rgba(22, 27, 34, 0.96) !important;
            border: 1px solid var(--border);
            color: #e6edf3;
        }

        /* Анимация для появления карточек */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.5s ease both;
        }

        .card:nth-child(1) {
            animation-delay: 0.05s;
        }

        .card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .metric-card .value {
            transition: all 0.3s ease;
        }

        .chart-container svg .glow-line {
            filter: drop-shadow(0 0 6px var(--accent-glow));
        }

        /* ---------- Спиннеры и состояния загрузки ---------- */
        .spinner-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 120px;
        }

        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid rgba(76, 139, 245, 0.2);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .chart-container {
            position: relative;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="dashboard-wrap">
        <div class="dashboard-header">
            <h1>
                {{ $name ?? 'Commerce Дашборд' }}
            </h1>
            <div class="btn-group" id="actions">
                @yield('buttons')
            </div>
        </div>

        <div class="section-body">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
