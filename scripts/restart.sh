#!/bin/bash

# Restart all services
echo "🔄 Restarting ManagePetro services..."

cd "$(dirname "$0")/../infra" || exit 1
docker-compose restart

echo "✅ Services restarted!"

