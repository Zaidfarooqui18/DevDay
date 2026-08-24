/**
 * DevDay Insights Controller
 * Anti-Digital Paper Edition: Chart.js configurations with earthy ink & paper palettes
 */

(function () {
    let chartInstances = {};

    window.DevDayInsights = {
        async init() {
            await this.loadData();
        },

        async loadData() {
            try {
                const response = await window.DevDayUI.request('/api/insights.php?action=summary');
                const data = response.data;
                this.renderMetrics(data.metrics);
                this.renderCharts(data);
            } catch (err) {
                window.DevDayUI.showToast('Failed to load insights: ' + err.message, 'error');
            }
        },

        renderMetrics(metrics) {
            if (!metrics) return;

            document.getElementById('insight-tasks-completed').textContent = metrics.tasks_completed_week || 0;
            document.getElementById('insight-focus-time').textContent = window.DevDayUI.formatMinutes(metrics.focus_minutes_week || 0);
            document.getElementById('insight-avg-day').textContent = window.DevDayUI.formatMinutes(Math.round((metrics.focus_minutes_week || 0) / 7));
            document.getElementById('insight-completion-rate').textContent = `${Math.round(metrics.completion_rate || 0)}%`;
        },

        renderCharts(data) {
            // Earthy Ink Palette
            const inkColors = {
                brown: '#8B4513',
                green: '#2D5A43',
                beige: '#C4A77D',
                amber: '#9A6218',
                red: '#B33927',
                ink: '#1A1A1A',
                pencil: '#4A4A4A',
                grid: '#E2D9CB',
                canvas: '#FAFAF8'
            };

            const chartFont = {
                family: "'JetBrains Mono', monospace",
                size: 11
            };

            // Destroy previous charts if reloaded
            Object.values(chartInstances).forEach(c => c.destroy());
            chartInstances = {};

            // 1. Focus Time by Day (Bar Chart)
            const ctx1 = document.getElementById('chart-focus-days')?.getContext('2d');
            if (ctx1 && data.daily_focus) {
                const labels = data.daily_focus.map(d => d.date_label || d.date);
                const values = data.daily_focus.map(d => (parseFloat(d.minutes || 0) / 60).toFixed(1));

                chartInstances.focusDays = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Focus Hours',
                            data: values,
                            backgroundColor: inkColors.brown,
                            borderColor: inkColors.ink,
                            borderWidth: 1.5,
                            borderRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: inkColors.ink,
                                titleFont: chartFont,
                                bodyFont: chartFont,
                                padding: 8,
                                callbacks: {
                                    label: (ctx) => `${ctx.raw} hours focused`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: chartFont, color: inkColors.pencil }
                            },
                            y: {
                                grid: { color: inkColors.grid },
                                ticks: { font: chartFont, color: inkColors.pencil, stepSize: 1 }
                            }
                        }
                    }
                });
            }

            // 2. Tasks Completed by Day (Line Chart)
            const ctx2 = document.getElementById('chart-tasks-days')?.getContext('2d');
            if (ctx2 && data.daily_tasks) {
                const labels = data.daily_tasks.map(d => d.date_label || d.date);
                const values = data.daily_tasks.map(d => parseInt(d.completed_count || 0));

                chartInstances.tasksDays = new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Tasks Done',
                            data: values,
                            borderColor: inkColors.green,
                            backgroundColor: 'rgba(45, 90, 67, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.2,
                            pointBackgroundColor: inkColors.green,
                            pointBorderColor: inkColors.ink,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: inkColors.ink,
                                titleFont: chartFont,
                                bodyFont: chartFont,
                                padding: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: chartFont, color: inkColors.pencil }
                            },
                            y: {
                                grid: { color: inkColors.grid },
                                ticks: { font: chartFont, color: inkColors.pencil, stepSize: 1 }
                            }
                        }
                    }
                });
            }

            // 3. Time Allocation by Category (Doughnut Chart)
            const ctx3 = document.getElementById('chart-category-time')?.getContext('2d');
            if (ctx3 && data.category_distribution) {
                const labels = data.category_distribution.map(d => d.category);
                const values = data.category_distribution.map(d => parseInt(d.minutes || 0));

                const colorPalette = [inkColors.brown, inkColors.green, inkColors.amber, inkColors.beige, inkColors.red, '#5C2D0C', '#1E4A35'];

                chartInstances.categoryTime = new Chart(ctx3, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colorPalette.slice(0, labels.length),
                            borderColor: inkColors.ink,
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: chartFont, color: inkColors.ink, padding: 12 }
                            },
                            tooltip: {
                                backgroundColor: inkColors.ink,
                                titleFont: chartFont,
                                bodyFont: chartFont,
                                callbacks: {
                                    label: (ctx) => ` ${ctx.label}: ${window.DevDayUI.formatMinutes(ctx.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            // 4. Time Allocation by Project (Doughnut Chart)
            const ctx4 = document.getElementById('chart-project-time')?.getContext('2d');
            if (ctx4 && data.project_distribution) {
                const labels = data.project_distribution.map(d => d.project_name || 'General Tasks');
                const values = data.project_distribution.map(d => parseInt(d.minutes || 0));

                const colorPalette = [inkColors.green, inkColors.brown, inkColors.beige, inkColors.amber, inkColors.red, '#4A5568'];

                chartInstances.projectTime = new Chart(ctx4, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colorPalette.slice(0, labels.length),
                            borderColor: inkColors.ink,
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: chartFont, color: inkColors.ink, padding: 12 }
                            },
                            tooltip: {
                                backgroundColor: inkColors.ink,
                                titleFont: chartFont,
                                bodyFont: chartFont,
                                callbacks: {
                                    label: (ctx) => ` ${ctx.label}: ${window.DevDayUI.formatMinutes(ctx.raw)}`
                                }
                            }
                        }
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
