#!/bin/bash

# Stop all services
echo "🛑 Stopping ManagePetro services..."

cd "$(dirname "$0")/../infra" || exit 1
docker-compose down

echo "✅ Services stopped!"

