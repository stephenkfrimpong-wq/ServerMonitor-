<?php

namespace ServerMonitor\WebSocket;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

class WebSocketServer
{
    private $monitor;
    private $clients = [];

    public function __construct($monitor, $port = 8080)
    {
        $this->monitor = $monitor;
        
        $worker = new Worker("websocket://0.0.0.0:$port");
        $worker->count = 4;
        
        $worker->onConnect = [$this, 'onConnect'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onClose = [$this, 'onClose'];
        
        // Broadcast metrics every 5 seconds
        $worker->onWorkerStart = [$this, 'onWorkerStart'];
    }

    public function onConnect(TcpConnection $connection)
    {
        $this->clients[$connection->id] = $connection;
        echo "New connection ({$connection->id})\n";
        
        // Send current server list
        $connection->send(json_encode([
            'type' => 'servers_list',
            'data' => $this->monitor->getServers()
        ]));
    }

    public function onMessage(TcpConnection $connection, $data)
    {
        $message = json_decode($data, true);
        
        switch ($message['type'] ?? '') {
            case 'subscribe_metrics':
                $connection->subscribed = true;
                break;
            case 'get_history':
                $history = $this->monitor->getHistoricalData($message['server_id']);
                $connection->send(json_encode([
                    'type' => 'history_data',
                    'data' => $history
                ]));
                break;
        }
    }

    public function onClose(TcpConnection $connection)
    {
        unset($this->clients[$connection->id]);
        echo "Connection closed ({$connection->id})\n";
    }

    public function onWorkerStart()
    {
        // Timer to collect and broadcast metrics
        \Workerman\Timer::add(5, function() {
            $this->broadcastMetrics();
        });
    }

    public function broadcastMetrics()
    {
        $metrics = $this->monitor->collectAllMetrics();
        $alerts = $this->monitor->checkAllAlerts();

        $message = json_encode([
            'type' => 'metrics_update',
            'data' => $metrics,
            'alerts' => $alerts,
            'timestamp' => time()
        ]);

        foreach ($this->clients as $connection) {
            if (isset($connection->subscribed)) {
                $connection->send($message);
            }
        }
    }

    public function run()
    {
        Worker::runAll();
    }
}
