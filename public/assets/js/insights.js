/**
 * DevDay Productivity Insights & Visualizations
 */

(function () {
    let focusChartInstance = null;
    let completedChartInstance = null;
    let categoryChartInstance = null;

    window.DevDayInsights = {
        async init() {
            await this.loadInsights();
        },

        async loadInsights() {
            try {
                const response = await window.DevDayUI.request('/api/insights.php');
                const data = response.data;

                this.renderSummary(data.summary);
                this.renderCharts(data.charts);
            } catch (err) {
                window.DevDayUI.showToast('Failed to load insights data.', 'error');
            }
        },

        renderSummary(summary) {
            if (!summary) return;

            document.getElementById('insight-tasks-completed').textContent = summary.tasks_completed;
            document.getElementById('insight-focus-time').textContent = summary.total_focus_formatted;
            document.getElementById('insight-avg-day').textContent = summary.avg_daily_formatted;
            document.getElementById('insight-completion-rate').textContent = `${Math.round(summary.completion_rate)}%`;
        },

        renderCharts(charts) {
            if (!charts || !window.Chart) return;

            // Common dark chart theme options
            const chartFont = { family: 'Inter', size: 11 };
            const gridColor = 'rgba(255, 255, 255, 0.05)';
            const tickColor = '#94a3b8';

            // 1. Focus Time by Day (Hours)
            const ctxFocus = document.getElementById('chart-focus-day')?.getContext('2d');
            if (ctxFocus) {
                if (focusChartInstance) focusChartInstance.destroy();
                focusChartInstance = new Chart(ctxFocus, {
                    type: 'bar',
                    data: {
                        labels: charts.days,
                        datasets: [{
                            label: 'Focus Hours',
                            data: charts.focus_hours_by_day,
                            backgroundColor: 'rgba(56, 189, 248, 0.75)',
                            borderColor: '#38bdf8',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `${ctx.parsed.y} hours (${Math.round(ctx.parsed.y * 60)} min)`
                                }
                            }
                        },
                        scales: {
                            x: { grid: { color: gridColor }, ticks: { color: tickColor, font: chartFont } },
                            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: chartFont }, beginAtZero: true }
                        }
                    }
                });
            }

            // 2. Tasks Completed by Day
            const ctxComp = document.getElementById('chart-tasks-day')?.getContext('2d');
            if (ctxComp) {
                if (completedChartInstance) completedChartInstance.destroy();
                completedChartInstance = new Chart(ctxComp, {
                    type: 'line',
                    data: {
                        labels: charts.days,
                        datasets: [{
                            label: 'Tasks Completed',
                            data: charts.completed_by_day,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#10b981',
                            pointRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: { grid: { color: gridColor }, ticks: { color: tickColor, font: chartFont } },
                            y: { 
                                grid: { color: gridColor }, 
                                ticks: { color: tickColor, font: chartFont, stepSize: 1 },
                                beginAtZero: true 
                            }
                        }
                    }
                });
            }

            // 3. Category Distribution
            const ctxCat = document.getElementById('chart-category-dist')?.getContext('2d');
            if (ctxCat) {
                if (categoryChartInstance) categoryChartInstance.destroy();

                const colors = ['#6366f1', '#ec4899', '#38bdf8', '#10b981', '#f59e0b', '#a855f7', '#94a3b8'];

                categoryChartInstance = new Chart(ctxCat, {
                    type: 'doughnut',
                    data: {
                        labels: charts.category_labels.length ? charts.category_labels : ['No Categories'],
                        datasets: [{
                            data: charts.category_counts.length ? charts.category_counts : [1],
                            backgroundColor: colors.slice(0, Math.max(1, charts.category_labels.length)),
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: tickColor, font: chartFont, boxWidth: 12, padding: 14 }
                            }
                        },
                        cutout: '72%'
                    }
                });
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'insights') {
            window.DevDayInsights.init();
        }
    });
})();
