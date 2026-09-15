$(document).ready(function () {
    var cfg = window.sgmsDashboard || {};
    if (!window.Chart) {
        return;
    }

    var visitsEl = document.getElementById('visitsChart');
    if (visitsEl) {
        new Chart(visitsEl, {
            type: 'line',
            data: {
                labels: cfg.labels || [],
                datasets: [
                    {
                        label: 'Visitantes',
                        data: cfg.visitors || [],
                        borderColor: '#7C3AED',
                        backgroundColor: 'rgba(124,58,237,.15)',
                        fill: true,
                        tension: .35,
                        pointRadius: 3
                    },
                    {
                        label: 'Vistas de página',
                        data: cfg.pageviews || [],
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37,99,235,.10)',
                        fill: false,
                        tension: .35,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    var deliveryEl = document.getElementById('deliveryChart');
    if (deliveryEl) {
        new Chart(deliveryEl, {
            type: 'doughnut',
            data: {
                labels: ['Entregados', 'No entregados', 'Pendientes'],
                datasets: [{
                    data: [cfg.delivered || 0, cfg.notDelivered || 0, cfg.inRoute || 0],
                    backgroundColor: ['#16A34A', '#DC2626', '#F59E0B'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
