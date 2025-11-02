#!/bin/bash

# Helper script to run Symfony commands
cd "$(dirname "$0")/../backend" || exit 1

if [ -z "$1" ]; then
    echo "Usage: ./scripts/backend.sh <command>"
    echo ""
    echo "Examples:"
    echo "  ./scripts/backend.sh console cache:clear"
    echo "  ./scripts/backend.sh console doctrine:migrations:migrate"
    echo "  ./scripts/backend.sh composer install"
    exit 1
fi

COMMAND="$1"
shift

case "$COMMAND" in
    console|symfony)
        # Check if running in Docker or locally
        if docker ps | grep -q symfony-app; then
            docker exec symfony-app php bin/console "$@"
        elif command -v php &> /dev/null; then
            php bin/console "$@"
        else
            echo "❌ PHP not found and Docker container not running"
            exit 1
        fi
        ;;
    composer)
        # Check if running in Docker or locally
        if docker ps | grep -q symfony-app; then
            docker exec symfony-app composer "$@"
        elif command -v composer &> /dev/null; then
            composer "$@"
        else
            echo "❌ Composer not found and Docker container not running"
            exit 1
        fi
        ;;
    *)
        echo "❌ Unknown command: $COMMAND"
        echo "Supported commands: console, composer"
        exit 1
        ;;
esac

