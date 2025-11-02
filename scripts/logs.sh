#!/bin/bash

# View logs for all services
cd "$(dirname "$0")/../infra" || exit 1

if [ -z "$1" ]; then
    echo "📋 Viewing logs for all services..."
    docker-compose logs -f
else
    echo "📋 Viewing logs for service: $1"
    docker-compose logs -f "$1"
fi

