/* ============================================================
   Commerce D3 Dashboard — тёмная тема с анимацией и акцентами
   ============================================================ */

function fmt(n) { return n.toLocaleString('ru-RU'); }
function fmtMoney(n) { return n.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' ₽'; }
/**
 * Вставляет индикатор загрузки в указанный контейнер.
 * @param {string} selector - CSS-селектор контейнера графика
 */
function showSpinner(selector) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '<div class="spinner-container"><div class="spinner"></div></div>';
}
function createTooltip() {
    return d3.select('body').append('div')
        .attr('class', 'd3-tooltip')
        .style('position', 'absolute')
        .style('padding', '8px 14px')
        .style('background', 'rgba(22,27,34,0.96)')
        .style('color', '#e6edf3')
        .style('border', '1px solid #30363d')
        .style('border-radius', '8px')
        .style('font-size', '12px')
        .style('pointer-events', 'none')
        .style('opacity', 0)
        .style('box-shadow', '0 8px 20px rgba(0,0,0,0.6)');
}

function showTooltip(tip, html, x, y) {
    tip.html(html)
        .style('left', (x + 10) + 'px')
        .style('top', (y - 20) + 'px')
        .style('opacity', 1);
}

function hideTooltip(tip) { tip.style('opacity', 0); }

const COLORS = {
    primary: '#4c8bf5',
    glow: 'rgba(76, 139, 245, 0.5)',
    grid: '#21262d',
    text: '#c9d1d9'
};

const CHART_PALETTE = [
    '#4c8bf5', '#a371f7', '#3fb950', '#d29922',
    '#f85149', '#79c0ff', '#f778ba', '#56d364',
    '#e3b341', '#ff7b72'
];

// ─── 1. График выручки (линия с анимацией появления) ───
function drawRevenueChart(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || data.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const margin = { top: 20, right: 20, bottom: 40, left: 60 };
    const width = el.clientWidth - margin.left - margin.right;
    const height = el.clientHeight - margin.top - margin.bottom;

    const svg = d3.select(selector)
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    const parsed = data.map(d => ({
        date: d3.timeParse('%Y-%m-%d')(d.date),
        total: +d.total
    })).sort((a, b) => a.date - b.date);

    const x = d3.scaleTime()
        .domain(d3.extent(parsed, d => d.date))
        .range([0, width]);
    const y = d3.scaleLinear()
        .domain([0, d3.max(parsed, d => d.total) * 1.05]).nice()
        .range([height, 0]);

    // Сетка
    svg.append('g').attr('class', 'grid')
        .call(d3.axisLeft(y).ticks(5).tickSize(-width).tickFormat(''))
        .call(g => g.select('.domain').remove())
        .call(g => g.selectAll('.tick line').attr('stroke', COLORS.grid).attr('stroke-width', 0.8));

    // Оси
    svg.append('g').attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(x).ticks(Math.min(parsed.length, 8)).tickFormat(d3.timeFormat('%d.%m')))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#8b949e').style('font-size', '11px');

    svg.append('g')
        .call(d3.axisLeft(y).tickFormat(d => d >= 1000 ? (d / 1000).toFixed(1) + 'k ₽' : d + ' ₽'))
        .call(g => g.select('.domain').remove())
        .selectAll('text').attr('fill', '#8b949e').style('font-size', '11px');

    // Градиент
    const gradId = 'revenue-grad-' + selector.replace('#', '');
    const gradient = svg.append('defs').append('linearGradient')
        .attr('id', gradId).attr('x1', '0%').attr('y1', '0%').attr('x2', '0%').attr('y2', '100%');
    gradient.append('stop').attr('offset', '0%').attr('stop-color', COLORS.primary).attr('stop-opacity', 0.3);
    gradient.append('stop').attr('offset', '100%').attr('stop-color', COLORS.primary).attr('stop-opacity', 0.02);

    // Область (анимация)
    const area = d3.area()
        .x(d => x(d.date)).y0(height).y1(d => y(d.total)).curve(d3.curveMonotoneX);
    svg.append('path').datum(parsed)
        .attr('fill', `url(#${gradId})`)
        .attr('d', area)
        .attr('opacity', 0)
        .transition().duration(800).attr('opacity', 1);

    // Линия (анимация рисования)
    const line = d3.line()
        .x(d => x(d.date)).y(d => y(d.total)).curve(d3.curveMonotoneX);
    const path = svg.append('path').datum(parsed)
        .attr('fill', 'none')
        .attr('stroke', COLORS.primary)
        .attr('stroke-width', 2.8)
        .attr('stroke-linecap', 'round')
        .attr('d', line);

    const totalLength = path.node().getTotalLength();
    path.attr('stroke-dasharray', totalLength + ' ' + totalLength)
        .attr('stroke-dashoffset', totalLength)
        .transition().duration(1200).ease(d3.easeCubic)
        .attr('stroke-dashoffset', 0);

    // Точки с пульсацией
    const tooltip = createTooltip();
    svg.selectAll('.dot')
        .data(parsed).enter()
        .append('circle')
        .attr('cx', d => x(d.date)).attr('cy', d => y(d.total))
        .attr('r', 0).attr('fill', COLORS.primary)
        .attr('stroke', '#fff').attr('stroke-width', 1)
        .transition().delay((d, i) => i * 50).duration(300)
        .attr('r', 4);

    // Ховер-зоны
    svg.selectAll('.hover-rect')
        .data(parsed).enter()
        .append('rect')
        .attr('x', d => x(d.date) - 3).attr('y', 0)
        .attr('width', 6).attr('height', height)
        .attr('fill', 'transparent')
        .on('mouseover', (event, d) => {
            showTooltip(tooltip, `<strong>${d3.timeFormat('%d.%m.%Y')(d.date)}</strong><br>Выручка: ${fmtMoney(d.total)}`, event.pageX, event.pageY);
        })
        .on('mousemove', (event) => {
            tooltip.style('left', (event.pageX + 10) + 'px').style('top', (event.pageY - 20) + 'px');
        })
        .on('mouseout', () => hideTooltip(tooltip));
}

// ─── 2. Воронка (анимированные столбцы) ───
function drawFunnel(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || data.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const margin = { top: 20, right: 60, bottom: 30, left: 140 };
    const width = el.clientWidth - margin.left - margin.right;
    const height = el.clientHeight - margin.top - margin.bottom;

    const svg = d3.select(selector).append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const maxVal = d3.max(data, d => d.count) || 1;
    const y = d3.scaleBand().domain(data.map(d => d.status)).range([0, height]).padding(0.15);
    const x = d3.scaleLinear().domain([0, maxVal]).range([0, width]);

    // Сетка
    svg.append('g')
        .call(d3.axisBottom(x).ticks(5).tickSize(-height).tickFormat(''))
        .call(g => g.select('.domain').remove())
        .call(g => g.selectAll('.tick line').attr('stroke', COLORS.grid));

    // Столбцы (анимация ширины)
    svg.selectAll('.bar')
        .data(data).enter()
        .append('rect')
        .attr('y', d => y(d.status)).attr('height', y.bandwidth())
        .attr('x', 0).attr('width', 0)
        .attr('fill', (d, i) => CHART_PALETTE[i % CHART_PALETTE.length])
        .attr('rx', 4)
        .style('filter', 'drop-shadow(0 2px 6px rgba(0,0,0,0.3))')
        .transition().duration(600).delay((d, i) => i * 60)
        .attr('width', d => x(d.count));

    // Оси
    svg.append('g').call(d3.axisLeft(y))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#c9d1d9').style('font-size', '12px');

    svg.append('g').attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(x).ticks(5))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#8b949e').style('font-size', '11px');

    // Значения (плавное появление)
    svg.selectAll('.label')
        .data(data).enter()
        .append('text')
        .attr('x', d => x(d.count) + 8).attr('y', d => y(d.status) + y.bandwidth() / 2)
        .attr('dy', '0.35em').style('font-size', '12px').style('fill', '#e6edf3')
        .style('opacity', 0)
        .text(d => d.count)
        .transition().delay((d, i) => i * 80).duration(300)
        .style('opacity', 1);

    const tooltip = createTooltip();
    svg.selectAll('.bar')
        .on('mouseover', (event, d) => showTooltip(tooltip, `<strong>${d.status}</strong><br>Заказов: ${d.count}`, event.pageX, event.pageY))
        .on('mousemove', (event) => tooltip.style('left', (event.pageX + 10) + 'px').style('top', (event.pageY - 20) + 'px'))
        .on('mouseout', () => hideTooltip(tooltip));
}

// ─── 3. Топ-10 товаров (аналогично воронке) ───
function drawTopProducts(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || data.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const margin = { top: 20, right: 140, bottom: 30, left: 190 };
    const width = el.clientWidth - margin.left - margin.right;
    const height = el.clientHeight - margin.top - margin.bottom;

    const svg = d3.select(selector).append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    data.sort((a, b) => b.quantity - a.quantity);
    const maxVal = d3.max(data, d => d.quantity) || 1;
    const y = d3.scaleBand().domain(data.map(d => d.name)).range([0, height]).padding(0.15);
    const x = d3.scaleLinear().domain([0, maxVal]).range([0, width]);

    svg.append('g')
        .call(d3.axisBottom(x).ticks(5).tickSize(-height).tickFormat(''))
        .call(g => g.select('.domain').remove())
        .call(g => g.selectAll('.tick line').attr('stroke', COLORS.grid));

    svg.selectAll('.bar')
        .data(data).enter()
        .append('rect')
        .attr('y', d => y(d.name)).attr('height', y.bandwidth())
        .attr('x', 0).attr('width', 0)
        .attr('fill', (d, i) => CHART_PALETTE[i % CHART_PALETTE.length])
        .attr('rx', 4)
        .style('filter', 'drop-shadow(0 2px 6px rgba(0,0,0,0.3))')
        .transition().duration(600).delay((d, i) => i * 50)
        .attr('width', d => x(d.quantity));

    svg.append('g').call(d3.axisLeft(y))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#c9d1d9').style('font-size', '11px');

    svg.append('g').attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(x).ticks(5))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#8b949e').style('font-size', '11px');

    svg.selectAll('.val-label')
        .data(data).enter()
        .append('text')
        .attr('x', d => x(d.quantity) + 8).attr('y', d => y(d.name) + y.bandwidth() / 2)
        .attr('dy', '0.35em').style('font-size', '12px').style('fill', '#e6edf3')
        .style('opacity', 0)
        .text(d => `${d.quantity} шт. / ${fmtMoney(d.total)}`)
        .transition().delay((d, i) => i * 60).duration(300)
        .style('opacity', 1);

    const tooltip = createTooltip();
    svg.selectAll('.bar')
        .on('mouseover', (event, d) => showTooltip(tooltip, `<strong>${d.name}</strong><br>Продано: ${d.quantity} шт.<br>Выручка: ${fmtMoney(d.total)}`, event.pageX, event.pageY))
        .on('mousemove', (event) => tooltip.style('left', (event.pageX + 10) + 'px').style('top', (event.pageY - 20) + 'px'))
        .on('mouseout', () => hideTooltip(tooltip));
}

// ─── 4. Treemap (плавное появление) ───
function drawTreemap(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || !data.children || data.children.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    const children = data.children.filter(d => d.value > 0);
    if (children.length === 0) { el.innerHTML = '<div class="empty-container">Нет продаж по категориям</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const width = el.clientWidth, height = el.clientHeight;
    const svg = d3.select(selector).append('svg').attr('width', width).attr('height', height);

    const root = d3.hierarchy({ name: 'root', children }).sum(d => d.value).sort((a, b) => b.value - a.value);
    d3.treemap().size([width, height]).padding(2)(root);

    const cell = svg.selectAll('g').data(root.leaves()).enter()
        .append('g').attr('transform', d => `translate(${d.x0},${d.y0})`)
        .style('opacity', 0);

    cell.append('rect')
        .attr('width', d => d.x1 - d.x0).attr('height', d => d.y1 - d.y0)
        .attr('fill', (d, i) => CHART_PALETTE[i % CHART_PALETTE.length]).attr('rx', 4)
        .style('filter', 'drop-shadow(0 2px 4px rgba(0,0,0,0.4))');

    cell.append('text')
        .attr('x', 4).attr('y', 14).text(d => d.data.name)
        .style('font-size', '11px').style('fill', '#fff').style('font-weight', 'bold');

    cell.transition().delay((d, i) => i * 50).duration(400)
        .style('opacity', 1);

    cell.append('title').text(d => `${d.data.name}: ${fmtMoney(d.data.value)}`);
}

// ─── 5. Circle Packing (пульсирующие пузыри) ───
function drawCirclePacking(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || !data.children || data.children.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    const children = data.children.filter(d => d.value > 0);
    if (children.length === 0) { el.innerHTML = '<div class="empty-container">Нет продаж по категориям</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const width = el.clientWidth, height = el.clientHeight;
    const svg = d3.select(selector).append('svg').attr('width', width).attr('height', height);

    const root = d3.hierarchy({ name: 'root', children }).sum(d => d.value).sort((a, b) => b.value - a.value);
    d3.pack().size([width - 10, height - 10]).padding(5)(root);

    const leaf = svg.selectAll('g').data(root.leaves()).enter()
        .append('g').attr('transform', d => `translate(${d.x + 5},${d.y + 5})`)
        .style('opacity', 0);

    leaf.append('circle')
        .attr('r', d => d.r)
        .attr('fill', (d, i) => CHART_PALETTE[i % CHART_PALETTE.length])
        .attr('opacity', 0.85)
        .style('filter', 'drop-shadow(0 2px 6px rgba(0,0,0,0.5))');

    leaf.append('text').attr('dy', '0.3em')
        .text(d => d.data.name.length > d.r * 0.8 ? '' : d.data.name)
        .style('font-size', d => Math.min(13, d.r / 3) + 'px')
        .style('text-anchor', 'middle').style('fill', '#fff').style('font-weight', '500');

    leaf.transition().delay((d, i) => i * 40).duration(400)
        .style('opacity', 1);

    leaf.append('title').text(d => `${d.data.name}: ${fmtMoney(d.data.value)}`);
}

// ─── 6. Sankey (анимированная прозрачность связей) ───
function drawSankey(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || !data.nodes || !data.links || data.nodes.length === 0 || data.links.length === 0) {
        el.innerHTML = '<div class="empty-container">Нет данных по переходам</div>'; return;
    }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const margin = { top: 10, right: 10, bottom: 10, left: 10 };
    const width = el.clientWidth - margin.left - margin.right;
    const height = el.clientHeight - margin.top - margin.bottom || 400;

    const svg = d3.select(selector).append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const sankey = d3.sankey().nodeWidth(20).nodePadding(15).extent([[0, 0], [width, height]]);
    const graph = sankey({
        nodes: data.nodes.map(d => Object.assign({}, d)),
        links: data.links.map(d => Object.assign({}, d))
    });
    if (!graph.nodes.length) return;

    const color = d3.scaleOrdinal(CHART_PALETTE);

    // Связи (анимация)
    svg.append('g')
        .attr('fill', 'none').attr('stroke-opacity', 0)
        .selectAll('path').data(graph.links).join('path')
        .attr('d', d3.sankeyLinkHorizontal())
        .attr('stroke', d => color(d.source.name))
        .attr('stroke-width', d => Math.max(1, d.width))
        .style('filter', 'drop-shadow(0 0 4px rgba(0,0,0,0.5))')
        .transition().duration(800)
        .attr('stroke-opacity', 0.3);

    // Узлы
    svg.append('g').selectAll('rect').data(graph.nodes).join('rect')
        .attr('x', d => d.x0).attr('y', d => d.y0)
        .attr('height', d => d.y1 - d.y0).attr('width', d => d.x1 - d.x0)
        .attr('fill', d => color(d.name)).attr('rx', 4)
        .style('filter', 'drop-shadow(0 2px 6px rgba(0,0,0,0.4))')
        .style('opacity', 0)
        .transition().delay((d, i) => i * 50).duration(400)
        .style('opacity', 1);

    // Подписи
    svg.append('g').selectAll('text').data(graph.nodes).join('text')
        .attr('x', d => d.x0 < width / 2 ? d.x1 + 6 : d.x0 - 6)
        .attr('y', d => (d.y1 + d.y0) / 2)
        .attr('dy', '0.35em')
        .attr('text-anchor', d => d.x0 < width / 2 ? 'start' : 'end')
        .text(d => d.name)
        .style('fill', '#e6edf3').style('font-size', '10px')
        .style('opacity', 0)
        .transition().delay((d, i) => i * 50).duration(400)
        .style('opacity', 1);

    // Tooltips
    svg.selectAll('.link-title').data(graph.links).join('title')
        .text(d => `${d.source.name} → ${d.target.name}: ${d.value} заказов`);
    svg.selectAll('.node-title').data(graph.nodes).join('title')
        .text(d => `${d.name}: ${d.value} заказов`);
}

// ─── 7. Тепловая карта (без изменений, можно добавить анимацию по желанию) ───
function drawHeatmap(selector, data) {
    const el = document.querySelector(selector);
    if (!el) return;
    el.innerHTML = '';
    if (!data || data.length === 0) { el.innerHTML = '<div class="empty-container">Нет данных</div>'; return; }
    if (el.clientWidth === 0 || el.clientHeight === 0) return;

    const margin = { top: 30, right: 30, bottom: 50, left: 70 };
    const width = el.clientWidth - margin.left - margin.right;
    const height = el.clientHeight - margin.top - margin.bottom;

    const svg = d3.select(selector).append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    const days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
    const hours = d3.range(0, 24);
    const matrix = Array.from({ length: 7 }, () => Array(24).fill(0));
    data.forEach(d => {
        if (d.day >= 0 && d.day < 7 && d.hour >= 0 && d.hour < 24) matrix[d.day][d.hour] = d.value;
    });
    const maxVal = d3.max(data, d => d.value) || 1;
    const x = d3.scaleBand().domain(hours).range([0, width]).padding(0.05);
    const y = d3.scaleBand().domain(days).range([0, height]).padding(0.05);
    const color = d3.scaleSequential(d3.interpolateYlOrRd).domain([0, maxVal]);

    svg.append('g').attr('transform', `translate(0,${height})`)
        .call(d3.axisBottom(x).tickFormat(d => d + ':00'))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#8b949e').style('font-size', '10px');

    svg.append('g').call(d3.axisLeft(y))
        .call(g => g.select('.domain').attr('stroke', '#30363d'))
        .selectAll('text').attr('fill', '#c9d1d9').style('font-size', '11px');

    svg.selectAll()
        .data(days.flatMap((day, di) => hours.map(hour => ({ day: di, hour, value: matrix[di][hour] }))))
        .enter()
        .append('rect')
        .attr('x', d => x(d.hour)).attr('y', d => y(days[d.day]))
        .attr('width', x.bandwidth()).attr('height', y.bandwidth())
        .attr('fill', d => d.value > 0 ? color(d.value) : '#161b22')
        .attr('rx', 3)
        .style('opacity', 0)
        .transition().duration(400)
        .style('opacity', 1);

    const tooltip = createTooltip();
    svg.selectAll('rect')
        .on('mouseover', (event, d) => showTooltip(tooltip, `<strong>${days[d.day]}, ${d.hour}:00</strong><br>Заказов: ${d.value}`, event.pageX, event.pageY))
        .on('mousemove', (event) => tooltip.style('left', (event.pageX + 10) + 'px').style('top', (event.pageY - 20) + 'px'))
        .on('mouseout', () => hideTooltip(tooltip));
}