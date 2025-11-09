#!/bin/bash

echo "🚀 ServerMonitor Installation"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 7.4 or higher."
    exit 1
fi

# Install Composer dependencies
echo "📦 Installing dependencies..."
composer install

# Setup database
echo "🗄️ Setting up database..."
mysql -u root -p < backend/database/schema.sql

# Create config file from template
cp config/config.example.php config/config.php
echo "✅ Please edit config/config.php with your settings"

echo "🎉 Installation complete!"
echo "👉 Start with: php backend/server.php"
echo "👉 Open frontend/index.html in your browser"
