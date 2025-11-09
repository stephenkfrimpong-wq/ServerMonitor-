CREATE TABLE servers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INT DEFAULT 22,
    type ENUM('linux', 'windows') DEFAULT 'linux',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    server_id INT,
    cpu_percent DECIMAL(5,2),
    memory_percent DECIMAL(5,2),
    disk_percent DECIMAL(5,2),
    network_in BIGINT,
    network_out BIGINT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id)
);

CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    server_id INT,
    type ENUM('cpu', 'memory', 'disk', 'network', 'offline'),
    message TEXT,
    threshold DECIMAL(5,2),
    current_value DECIMAL(5,2),
    status ENUM('triggered', 'resolved'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
