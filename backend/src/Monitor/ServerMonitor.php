<?php

namespace ServerMonitor\Monitor;

class ServerMonitor
{
    private $db;
    private $servers = [];

    public function __construct($database)
    {
        $this->db = $database;
        $this->loadServers();
    }

    public function loadServers()
    {
        $stmt = $this->db->query("SELECT * FROM servers WHERE active = 1");
        $this->servers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function collectMetrics($server)
    {
        $metrics = [
            'server_id' => $server['id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        switch ($server['type']) {
            case 'linux':
                $metrics = array_merge($metrics, $this->getLinuxMetrics($server));
                break;
            case 'windows':
                $metrics = array_merge($metrics, $this->getWindowsMetrics($server));
                break;
        }

        return $metrics;
    }

    private function getLinuxMetrics($server)
    {
        // CPU usage
        $cpu = shell_exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}'");
        $cpu = floatval($cpu);

        // Memory usage
        $memory = shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'");
        $memory = floatval($memory);

        // Disk usage
        $disk = shell_exec("df / | awk 'NR==2 {print $5}' | sed 's/%//'");
        $disk = floatval($disk);

        // Network I/O
        $network = $this->getNetworkStats();

        return [
            'cpu_percent' => $cpu,
            'memory_percent' => $memory,
            'disk_percent' => $disk,
            'network_in' => $network['in'],
            'network_out' => $network['out']
        ];
    }

    private function getNetworkStats()
    {
        // Get network statistics
        $stats = file('/proc/net/dev');
        $totalIn = 0;
        $totalOut = 0;

        for ($i = 2; $i < count($stats); $i++) {
            $parts = preg_split('/\s+/', trim($stats[$i]));
            if (count($parts) >= 10) {
                $totalIn += $parts[1];
                $totalOut += $parts[9];
            }
        }

        return ['in' => $totalIn, 'out' => $totalOut];
    }

    public function saveMetrics($metrics)
    {
        $sql = "INSERT INTO metrics (server_id, cpu_percent, memory_percent, disk_percent, network_in, network_out) 
                VALUES (:server_id, :cpu_percent, :memory_percent, :disk_percent, :network_in, :network_out)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($metrics);

        return $this->db->lastInsertId();
    }

    public function checkAlerts($metrics)
    {
        $alerts = [];

        // CPU alert
        if ($metrics['cpu_percent'] > 80) {
            $alerts[] = [
                'type' => 'cpu',
                'message' => "High CPU usage: {$metrics['cpu_percent']}%",
                'threshold' => 80,
                'current_value' => $metrics['cpu_percent']
            ];
        }

        // Memory alert
        if ($metrics['memory_percent'] > 85) {
            $alerts[] = [
                'type' => 'memory',
                'message' => "High Memory usage: {$metrics['memory_percent']}%",
                'threshold' => 85,
                'current_value' => $metrics['memory_percent']
            ];
        }

        // Disk alert
        if ($metrics['disk_percent'] > 90) {
            $alerts[] = [
                'type' => 'disk',
                'message' => "High Disk usage: {$metrics['disk_percent']}%",
                'threshold' => 90,
                'current_value' => $metrics['disk_percent']
            ];
        }

        return $alerts;
    }
}
