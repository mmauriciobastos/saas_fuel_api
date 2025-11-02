#!/bin/bash

# Initial setup script
echo "🔧 Setting up ManagePetro monorepo..."

# Make scripts executable
echo "📝 Making scripts executable..."
chmod +x scripts/*.sh

# Start services
echo "🐳 Starting Docker services..."
./scripts/start.sh

# Wait a bit for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 5

# Install backend dependencies
if [ -f "backend/composer.json" ]; then
    echo "📦 Installing backend dependencies..."
    cd backend || exit 1
    
    # Check if composer is available
    if command -v composer &> /dev/null; then
        composer install
    else
        echo "⚠️  Composer not found. Installing dependencies via Docker..."
        docker exec symfony-app composer install
    fi
    
    cd ..
fi

echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "  - Access the application: http://localhost:8000"
echo "  - Access pgAdmin: http://localhost:8081"
echo "  - Run migrations: cd backend && php bin/console doctrine:migrations:migrate"

