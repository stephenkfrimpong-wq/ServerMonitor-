<?php

return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'servermonitor',
        'username' => 'monitor_user',
        'password' => 'secure_password'
    ],
    
    'monitoring' => [
        'interval' => 5, // seconds
        'retention_days' => 30
    ],
    
    'alerts' => [
        'email' => [
            'enabled' => false,
            'smtp_host' => 'smtp.gmail.com',
            'username' => 'your-email@gmail.com',
            'password' => 'your-app-password',
            'from' => 'alerts@servermonitor.com',
            'to' => 'admin@yourcompany.com'
        ],
        
        'slack' => [
            'enabled' => false,
            'webhook_url' => 'https://hooks.slack.com/services/...',
            'channel' => '#server-alerts'
        ],
        
        'webhook' => [
            'enabled' => false,
            'url' => 'https://your-webhook-url.com/alert'
        ]
    ],
    
    'websocket' => [
        'port' => 8080,
        'allowed_origins' => ['http://localhost:3000', 'http://yourdomain.com']
    ]
];
