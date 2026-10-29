/**
 * Chart Visualizer Engine
 * Mengelola Chart.js dengan skala ganda (Dual-Axis) dan Canvas Data Labels Plugin
 * Sumbu Kiri: Waktu Split (s) & Stroke Rate (SPM)
 * Sumbu Kanan: Laju (m/s) & DPS (m) dengan skala desimal presisi stepSize 0.25
 */

// Canvas Data Labels Plugin (Direct Point Render)
const pointDataLabelsPlugin = {
    id: 'pointDataLabels',
    afterDatasetsDraw(chart) {
        const ctx = chart.ctx;
        chart.data.datasets.forEach((dataset, datasetIdx) => {
            const meta = chart.getDatasetMeta(datasetIdx);
            if (meta.hidden) return;
            meta.data.forEach((element, index) => {
                // Jangan cetak label pada titik start 0m jika nilainya 0
                if (index === 0 && dataset.data[index] === 0) return;
                const val = dataset.data[index];
                if (val === undefined || val === null || val === 0) return;

                let text = '';
                if (dataset.yAxisID === 'yKinematics') {
                    text = Number(val).toFixed(2);
                } else if (dataset.label.includes('Waktu')) {
                    text = Number(val).toFixed(2) + 's';
                } else {
                    text = Number(val).toFixed(1);
                }

                ctx.save();
                ctx.font = 'bold 8.5px Inter, -apple-system, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                // Stagger Y offsets: Waktu/SR di atas (-10px), Laju/DPS di bawah (+11px)
                const yOffset = (datasetIdx === 0 || datasetIdx === 1) ? -10 : 11;
                const targetY = element.y + yOffset;

                const textWidth = ctx.measureText(text).width;
                ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
                ctx.fillRect(element.x - textWidth / 2 - 2, targetY - 5, textWidth + 4, 10);

                ctx.fillStyle = dataset.borderColor;
                ctx.fillText(text, element.x, targetY);
                ctx.restore();
            });
        });
    }
};

function initChart() {
    const canvas = document.getElementById('screenChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const defaultLabels = poolLength === 50 ? ['0m', '50m', '100m'] : ['0m', '25m', '50m', '75m', '100m'];
    
    chartInstance = new Chart(ctx, {
        type: 'line',
        plugins: [pointDataLabelsPlugin],
        data: {
            labels: defaultLabels,
            datasets: [
                {
                    label: 'Waktu Split (s)',
                    data: [0, 0, 0],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.25,
                    yAxisID: 'yPrimary',
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderWidth: 2.5
                },
                {
                    label: 'Stroke Rate (SPM)',
                    data: [0, 0, 0],
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.25,
                    yAxisID: 'yPrimary',
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderWidth: 2.5
                },
                {
                    label: 'Laju (m/s)',
                    data: [0, 0, 0],
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.25,
                    yAxisID: 'yKinematics',
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderWidth: 2.5
                },
                {
                    label: 'DPS (m)',
                    data: [0, 0, 0],
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.25,
                    yAxisID: 'yKinematics',
                    pointRadius: 4.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderWidth: 2.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (items) => {
                            const lbl = items[0].label;
                            return lbl === '0m' ? 'Titik Start (0m)' : `Posisi Jarak: ${lbl}`;
                        },
                        label: (item) => {
                            const val = Number(item.raw) || 0;
                            if (item.dataset.label.includes('DPS')) {
                                return `DPS: ${val.toFixed(2)} m`;
                            } else if (item.dataset.label.includes('Laju')) {
                                return `Laju: ${val.toFixed(2)} m/s`;
                            } else if (item.dataset.label.includes('SR')) {
                                return `Stroke Rate: ${val.toFixed(1)} SPM`;
                            } else {
                                return `Waktu Split: ${val.toFixed(2)} dtk`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: '#e2e8f0', lineWidth: 1 },
                    ticks: { font: { weight: 'bold', size: 10 }, color: '#334155' }
                },
                yPrimary: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', lineWidth: 1 },
                    title: {
                        display: true,
                        text: 'Waktu (s) & SR (SPM)',
                        color: '#1e3a8a',
                        font: { weight: 'bold', size: 10 }
                    },
                    ticks: {
                        stepSize: 10,
                        font: { size: 9, weight: '600' },
                        color: '#475569'
                    }
                },
                yKinematics: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    title: {
                        display: true,
                        text: 'Laju (m/s) & DPS (m)',
                        color: '#047857',
                        font: { weight: 'bold', size: 10 }
                    },
                    ticks: {
                        stepSize: 0.25,
                        font: { size: 9, weight: '700', family: 'monospace' },
                        color: '#047857',
                        callback: (val) => Number(val).toFixed(2)
                    }
                }
            }
        }
    });
}

function updateChart(labels, times, srs, vels, dpss) {
    if (!chartInstance) return;
    chartInstance.data.labels = labels;
    chartInstance.data.datasets[0].data = times;
    chartInstance.data.datasets[1].data = srs;
    chartInstance.data.datasets[2].data = vels;
    chartInstance.data.datasets[3].data = dpss;
    chartInstance.update();
}

window.RaceChart = {
    initChart,
    updateChart,
    pointDataLabelsPlugin
};
