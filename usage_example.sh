# Start the monitoring server
php backend/server.php

# Access the dashboard
# Open frontend/index.html in a web server

# Add a server to monitor (via API)
curl -X POST http://localhost:8081/api/servers \
  -H "Content-Type: application/json" \
  -d '{"name": "Production Web", "host": "192.168.1.100", "type": "linux"}'
