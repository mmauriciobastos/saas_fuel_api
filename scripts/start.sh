#!/bin/bash

# Start all services
echo "🚀 Starting ManagePetro services..."

cd "$(dirname "$0")/../infra" || exit 1
docker-compose up -d

echo "✅ Services started!"
echo "📱 Application: http://localhost:8000"
echo "🗄️  pgAdmin: http://localhost:8081"

