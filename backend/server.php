#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ServerMonitor\Monitor\ServerMonitor;
use ServerMonitor\WebSocket\WebSocketServer;
use ServerMonitor\Alerts\AlertManager;

// Load configuration
$config = require __DIR__ . '/../config/config.php';

// Initialize database connection
$db = new PDO(
    "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']}",
    $config['database']['username'],
    $config['database']['password']
);

// Initialize components
$monitor = new ServerMonitor($db);
$alertManager = new AlertManager($db, $config['alerts']);
$websocketServer = new WebSocketServer($monitor, $config['websocket']['port']);

echo "ServerMonitor starting...\n";
echo "WebSocket server listening on port {$config['websocket']['port']}\n";

// Start the WebSocket server
$websocketServer->run();
