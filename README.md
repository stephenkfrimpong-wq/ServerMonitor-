🖥️ ServerMonitor - Real-time Server Monitoring Dashboard

A powerful, real-time server monitoring system built with vanilla PHP that provides beautiful insights into your system's performance with instant alerts and historical analytics.

https://img.shields.io/badge/PHP-8.2%252B-777BB4?style=for-the-badge&logo=php&logoColor=white

https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white

https://img.shields.io/badge/WebSocket-Real--time-010101?style=for-the-badge&logo=websocket&logoColor=white

🌟 Features
📊 Real-time Monitoring

    Live Metrics: CPU, memory, disk, and network usage updated every 5 seconds

    Beautiful Charts: Interactive charts with Chart.js showing historical trends

    Multi-Server Support: Monitor multiple servers from a single dashboard

    WebSocket Powered: Real-time updates without page refresh

🔔 Smart Alert System

    Configurable Thresholds: Set custom limits for CPU, memory, and disk usage

    Multiple Channels: Email, Slack, and webhook notifications

    Alert History: Track all triggered alerts with resolution status

    Visual Indicators: Color-coded metrics and warning badges

🛠️ Technical Excellence

    Zero Dependencies: Pure PHP backend with vanilla JavaScript frontend

    Database Efficient: Optimized metric storage with automatic data retention

    Cross-Platform: Supports Linux and Windows servers

    RESTful API: Extensible architecture for integrations

🚀 Quick Start
Prerequisites

    PHP 8.2 or higher

    MySQL 8.0 or higher

    Composer (for dependency management)

Installation

    Clone the repository
    bash

git clone https://github.com/stephenkfrimpong-wq/servermonitor.git

cd servermonitor

Install PHP dependencies
bash

composer install

Set up the database
bash

mysql -u root -p < backend/database/schema.sql

Configure the application
bash

cp config/config.example.php config/config.php
# Edit config/config.php with your database credentials and settings

Start the monitoring server
bash

php backend/server.php

Launch the dashboard
bash

# In a new terminal
php -S localhost:8081 -t frontend

    Access the dashboard
    Open http://localhost:8081 in your browser

📸 Dashboard Preview

The ServerMonitor dashboard provides:

    Server Overview Cards: At-a-glance view of all monitored servers with color-coded status indicators

    Real-time Charts: Live updating line charts for CPU and memory usage

    Alert Panel: Immediate visibility of any system alerts with notification support

    Historical Data: Scrollable timeline of past 20 data points for trend analysis

    Responsive Design: Optimized for both desktop and mobile viewing

🏗️ Architecture text

servermonitor/

├── backend/

│   ├── src/

│   │   ├── Monitor/          # Core monitoring logic

│   │   ├── WebSocket/        # Real-time communication

│   │   └── Alerts/           # Alert management system

│   └── server.php           # Main application entry point

├── frontend/

│   ├── index.html           # Dashboard interface

│   ├── dashboard.js         # Real-time chart updates

│   └── styles.css           # Responsive styling


├── config/

│   └── config.php           # Application configuration

└── scripts/

    └── install.sh           # Deployment scripts

Core Components

    ServerMonitor: Collects and processes system metrics

    WebSocketServer: Handles real-time client connections

    AlertManager: Manages threshold checks and notifications

    Dashboard: Beautiful frontend with Chart.js visualizations

⚙️ Configuration
Basic Setup

Edit config/config.php to match your environment:
php

return [

    'database' => [
        'host' => 'localhost',
        'dbname' => 'servermonitor',
        'username' => 'monitor_user',
        'password' => 'your_password'
    ],
    'monitoring' => [
        'interval' => 5,          // Collection interval in seconds
        'retention_days' => 30    // How long to keep historical data
    ]
];

Alert Thresholds

Configure when alerts should trigger:
php

'alerts' => [

    'thresholds' => [
        'cpu' => 80,        // Alert when CPU > 80%
        'memory' => 85,     // Alert when Memory > 85%
        'disk' => 90        // Alert when Disk > 90%
    ]
]

Notification Channels

Set up multiple alert destinations:
php

'email' => [

    'enabled' => true,
    'smtp_host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com',
    'password' => 'app-password',
    'to' => 'admin@yourcompany.com'
],
'slack' => [

    'enabled' => true,
    'webhook_url' => 'https://hooks.slack.com/services/...'
]

🔌 API Usage
Add a New Server
bash

curl -X POST http://localhost:8081/api/servers \

  -H "Content-Type: application/json" \
  
  -d '{
  
    "name": "Production Web Server",
    "host": "192.168.1.100", 
    "type": "linux"
  
  }'

Get Server Metrics
bash

curl http://localhost:8081/api/servers/1/metrics

🎯 Usage Examples
Monitor Local Development
bash

# The dashboard automatically monitors the local system
php backend/server.php

Monitor Remote Servers

    Add remote servers via the API or database

    Ensure SSH access for metric collection

    View all servers in the unified dashboard

Custom Alert Rules
php

// Add custom alert logic in AlertManager.php
if ($metrics['cpu_percent'] > 95) {
    $this->triggerCriticalAlert('CPU critically high!');
}

📈 Supported Metrics
Metric	Description	Collection Method

CPU Usage	Percentage of CPU utilization	/proc/stat analysis

Memory Usage	RAM consumption percentage	free command parsing

Disk Usage	Storage capacity percentage	df command output

Network I/O	Bytes in/out per interval	/proc/net/dev parsing

Uptime	System running duration	/proc/uptime reading

🛠️ Development

Running Tests

bash

composer test

Adding New Metrics

    Extend the ServerMonitor class

    Add collection method in the appropriate OS handler

    Update database schema if needed

    Add frontend chart component

Customizing the Dashboard

    Modify frontend/dashboard.js for chart behavior

    Update frontend/styles.css for styling changes

    Extend frontend/index.html for new UI components

🐛 Troubleshooting
Common Issues

WebSocket connection fails

    Check if port 8080 is available

    Verify firewall settings

    Ensure Workerman is properly installed

No metrics appearing

    Verify database connection in config

    Check server process is running

    Review logs in logs/ directory

High system resource usage

    Increase monitoring interval in config

    Reduce data retention period

    Optimize database queries

Logs and Debugging

    Application logs: logs/websocket.log

    Dashboard logs: logs/dashboard.log

    Database logs: Check MySQL error log

🤝 Contributing

We love contributions! Here's how to help:

    Fork the repository

    Create a feature branch: git checkout -b amazing-feature

    Commit your changes: git commit -m 'Add amazing feature'

    Push to the branch: git push origin amazing-feature

    Open a Pull Request

Development Priorities

    Add Docker support

    Create plugin system for custom metrics

    Add authentication and multi-user support

    Develop mobile app companion

    Create export functionality for reports

📄 License

This project is licensed under the MIT License - see the LICENSE file for details.
🙏 Acknowledgments

    Chart.js for beautiful, responsive charts

    Workerman for high-performance WebSocket server

    Bootstrap for responsive dashboard components

    PHP Mailer for reliable email notifications
