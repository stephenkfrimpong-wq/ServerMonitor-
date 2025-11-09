class ServerMonitorDashboard {
    constructor() {
        this.ws = null;
        this.servers = {};
        this.charts = {};
        this.initializeWebSocket();
        this.initializeCharts();
    }

    initializeWebSocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.hostname}:8080`;
        
        this.ws = new WebSocket(wsUrl);
        
        this.ws.onopen = () => {
            this.updateConnectionStatus('connected');
            this.ws.send(JSON.stringify({ type: 'subscribe_metrics' }));
        };

        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.ws.onclose = () => {
            this.updateConnectionStatus('disconnected');
            setTimeout(() => this.initializeWebSocket(), 5000);
        };
    }

    handleMessage(data) {
        switch (data.type) {
            case 'metrics_update':
                this.updateMetrics(data.data);
                this.handleAlerts(data.alerts);
                document.getElementById('last-update').textContent = 
                    `Last update: ${new Date().toLocaleTimeString()}`;
                break;
            case 'servers_list':
                this.initializeServerCards(data.data);
                break;
        }
    }

    initializeServerCards(servers) {
        const container = document.getElementById('servers-container');
        
        servers.forEach(server => {
            const card = this.createServerCard(server);
            container.innerHTML += card;
            this.servers[server.id] = server;
        });
    }

    createServerCard(server) {
        return `
            <div class="col-md-4 mb-4">
                <div class="card metric-card normal" id="server-${server.id}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">${server.name}</h6>
                        <span class="badge bg-secondary">${server.type}</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="metric-value" id="cpu-${server.id}">--%</div>
                                <small class="text-muted">CPU</small>
                            </div>
                            <div class="col-4">
                                <div class="metric-value" id="memory-${server.id}">--%</div>
                                <small class="text-muted">Memory</small>
                            </div>
                            <div class="col-4">
                                <div class="metric-value" id="disk-${server.id}">--%</div>
                                <small class="text-muted">Disk</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    updateMetrics(metrics) {
        metrics.forEach(metric => {
            this.updateMetricElement(`cpu-${metric.server_id}`, metric.cpu_percent);
            this.updateMetricElement(`memory-${metric.server_id}`, metric.memory_percent);
            this.updateMetricElement(`disk-${metric.server_id}`, metric.disk_percent);
            
            this.updateCardStatus(metric.server_id, metric);
            this.updateCharts(metric);
        });
    }

    updateMetricElement(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = `${value ? value.toFixed(1) : '--'}%`;
            
            // Color coding
            if (value > 80) element.className = 'metric-value text-danger';
            else if (value > 60) element.className = 'metric-value text-warning';
            else element.className = 'metric-value text-success';
        }
    }

    updateCardStatus(serverId, metrics) {
        const card = document.getElementById(`server-${serverId}`);
        if (!card) return;

        // Determine worst status
        const maxUsage = Math.max(
            metrics.cpu_percent || 0,
            metrics.memory_percent || 0,
            metrics.disk_percent || 0
        );

        card.className = card.className.replace(/(critical|warning|normal)/g, '');
        
        if (maxUsage > 80) card.classList.add('critical');
        else if (maxUsage > 60) card.classList.add('warning');
        else card.classList.add('normal');
    }

    initializeCharts() {
        const cpuCtx = document.getElementById('cpuChart').getContext('2d');
        const memoryCtx = document.getElementById('memoryChart').getContext('2d');

        this.charts.cpu = new Chart(cpuCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'CPU Usage %',
                    data: [],
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        min: 0,
                        max: 100
                    }
                }
            }
        });

        this.charts.memory = new Chart(memoryCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Memory Usage %',
                    data: [],
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    }

    updateCharts(metric) {
        const timestamp = new Date().toLocaleTimeString();
        
        // Update CPU chart
        this.updateChart(this.charts.cpu, timestamp, metric.cpu_percent);
        
        // Update Memory chart
        this.updateChart(this.charts.memory, timestamp, metric.memory_percent);
    }

    updateChart(chart, label, data) {
        chart.data.labels.push(label);
        chart.data.datasets[0].data.push(data);
        
        // Keep only last 20 data points
        if (chart.data.labels.length > 20) {
            chart.data.labels.shift();
            chart.data.datasets[0].data.shift();
        }
        
        chart.update();
    }

    handleAlerts(alerts) {
        if (alerts.length > 0) {
            const alertPanel = document.getElementById('alert-panel');
            const alertMessage = document.getElementById('alert-message');
            
            alertMessage.textContent = alerts[0].message;
            alertPanel.style.display = 'block';
            
            // Send notification
            if (Notification.permission === 'granted') {
                new Notification('Server Alert', {
                    body: alerts[0].message
                });
            }
        }
    }

    updateConnectionStatus(status) {
        const element = document.getElementById('connection-status');
        element.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        element.className = `badge bg-${status === 'connected' ? 'success' : 'danger'}`;
    }
}

// Request notification permission
if ('Notification' in window) {
    Notification.requestPermission();
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new ServerMonitorDashboard();
});

function dismissAlert() {
    document.getElementById('alert-panel').style.display = 'none';
}
