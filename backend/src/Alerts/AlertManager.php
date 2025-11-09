<?php

namespace ServerMonitor\Alerts;

class AlertManager
{
    private $db;
    private $config;

    public function __construct($database, $config)
    {
        $this->db = $database;
        $this->config = $config;
    }

    public function processAlert($alert, $server)
    {
        // Save to database
        $this->saveAlert($alert, $server);

        // Send notifications
        $this->sendNotifications($alert, $server);
    }

    private function saveAlert($alert, $server)
    {
        $sql = "INSERT INTO alerts (server_id, type, message, threshold, current_value, status) 
                VALUES (:server_id, :type, :message, :threshold, :current_value, 'triggered')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'server_id' => $server['id'],
            'type' => $alert['type'],
            'message' => $alert['message'],
            'threshold' => $alert['threshold'],
            'current_value' => $alert['current_value']
        ]);
    }

    private function sendNotifications($alert, $server)
    {
        $message = "🚨 SERVER ALERT\n";
        $message .= "Server: {$server['name']}\n";
        $message .= "Alert: {$alert['message']}\n";
        $message .= "Time: " . date('Y-m-d H:i:s');

        // Email notification
        if ($this->config['email']['enabled']) {
            $this->sendEmail($message);
        }

        // Slack notification
        if ($this->config['slack']['enabled']) {
            $this->sendSlack($message);
        }

        // Webhook notification
        if ($this->config['webhook']['enabled']) {
            $this->sendWebhook($alert, $server);
        }
    }

    private function sendEmail($message)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        
        try {
            $mail->isSMTP();
            $mail->Host = $this->config['email']['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['email']['username'];
            $mail->Password = $this->config['email']['password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($this->config['email']['from']);
            $mail->addAddress($this->config['email']['to']);
            $mail->Subject = 'ServerMonitor Alert';
            $mail->Body = $message;

            $mail->send();
        } catch (\Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
        }
    }

    private function sendSlack($message)
    {
        $data = [
            'text' => $message,
            'channel' => $this->config['slack']['channel'],
            'username' => 'ServerMonitor'
        ];

        $this->sendHttpRequest($this->config['slack']['webhook_url'], $data);
    }

    private function sendWebhook($alert, $server)
    {
        $data = [
            'alert' => $alert,
            'server' => $server,
            'timestamp' => time()
        ];

        $this->sendHttpRequest($this->config['webhook']['url'], $data);
    }

    private function sendHttpRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
