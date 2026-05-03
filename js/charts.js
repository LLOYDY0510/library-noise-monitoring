// ============================================================
//  charts.js
// Chart.js 4.4.1 + chartjs-plugin-annotation
// FIX: explicitly register the annotation plugin after CDN load
// ============================================================

/**
 * @param {string}   canvasId
 * @param {Array}    datasets    [{label, data:[]}]
 * @param {Array}    labels
 * @param {number}   [warnAt=40]
 * @param {number}   [critAt=60]
 */
function renderNoiseChart(canvasId, datasets, labels, warnAt = 40, critAt = 60) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    // Register the annotation plugin — required when loading from CDN
    // (auto-registration only works with bundlers, not plain CDN scripts)
    if (window.ChartAnnotation) {
        Chart.register(window.ChartAnnotation);
    }

    const COLORS = ['#60a5fa', '#fbbf24', '#34d399', '#a78bfa', '#f87171'];
    const FONT   = "'Plus Jakarta Sans', sans-serif";

    const hasData = datasets.length > 0 && datasets.some(d => d.data && d.data.length > 0);

    const chartDatasets = hasData
        ? datasets.map((ds, i) => ({
            label:            ds.label || `Zone ${i + 1}`,
            data:             ds.data  || [],
            borderColor:      COLORS[i % COLORS.length],
            backgroundColor:  COLORS[i % COLORS.length] + '22',
            borderWidth:      2,
            pointRadius:      3,
            pointHoverRadius: 5,
            tension:          0.4,
            fill:             true,
          }))
        : [{
            label:            'No alert data yet',
            data:             [],
            borderColor:      '#475569',
            backgroundColor:  '#1e293b',
            borderWidth:      1,
          }];

    const annotations = {
        warnLine: {
            type:        'line',
            yMin:        warnAt,
            yMax:        warnAt,
            borderColor: '#fbbf24',
            borderWidth: 1.5,
            borderDash:  [6, 4],
            label: {
                content:         `Warn ${warnAt} dB`,
                display:         true,
                position:        'start',
                color:           '#fbbf24',
                font:            { size: 10, family: FONT, weight: '600' },
                backgroundColor: 'transparent',
                padding:         { x: 4, y: 2 },
            }
        },
        critLine: {
            type:        'line',
            yMin:        critAt,
            yMax:        critAt,
            borderColor: '#f87171',
            borderWidth: 1.5,
            borderDash:  [6, 4],
            label: {
                content:         `Crit ${critAt} dB`,
                display:         true,
                position:        'start',
                color:           '#f87171',
                font:            { size: 10, family: FONT, weight: '600' },
                backgroundColor: 'transparent',
                padding:         { x: 4, y: 2 },
            }
        }
    };

    new Chart(canvas, {
        type: 'line',
        data: { labels: labels || [], datasets: chartDatasets },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            interaction:         { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display:  true,
                    position: 'bottom',
                    labels: {
                        color:         '#94a3b8',
                        font:          { family: FONT, size: 11 },
                        boxWidth:      12,
                        padding:       16,
                        usePointStyle: true,
                    }
                },
                annotation: { annotations },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor:      '#f1f5f9',
                    bodyColor:       '#94a3b8',
                    borderColor:     '#334155',
                    borderWidth:     1,
                    padding:         10,
                    titleFont:       { family: FONT, size: 12, weight: '700' },
                    bodyFont:        { family: FONT, size: 11 },
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y?.toFixed(1) ?? 0} dB`
                    }
                }
            },
            scales: {
                x: {
                    grid:  { color: '#1e293b' },
                    border:{ color: '#334155' },
                    ticks: {
                        color:        '#64748b',
                        font:         { family: FONT, size: 10 },
                        maxTicksLimit: 8,
                    }
                },
                y: {
                    min:   0,
                    max:   90,
                    grid:  { color: '#1e293b' },
                    border:{ color: '#334155' },
                    ticks: {
                        color:    '#64748b',
                        font:     { family: FONT, size: 10 },
                        callback: v => v + ' dB',
                        stepSize: 15,
                    }
                }
            }
        }
    });
}
