# ManagePetro Monorepo

A monorepo containing the ManagePetro application backend and infrastructure configurations.

## 📁 Project Structure

```
managepetro/
├── backend/          # Symfony application
│   ├── src/          # Application source code
│   ├── config/       # Symfony configuration
│   ├── public/       # Web entry point
│   └── ...
├── infra/            # Infrastructure as Code
│   ├── docker-compose.yml
│   ├── Dockerfile
│   └── nginx.conf
└── scripts/          # Helper scripts
```

## 🚀 Getting Started

### Prerequisites

- Docker and Docker Compose
- Git

### Quick Start

1. **Start the development environment:**
   ```bash
   cd infra
   docker-compose up -d
   ```

2. **Access the application:**
   - Application: http://localhost:8000
   - pgAdmin: http://localhost:8081
   - Database: localhost:5432

3. **Install backend dependencies:**
   ```bash
   cd backend
   composer install
   ```

### Development Workflow

#### Backend Development

```bash
# Navigate to backend
cd backend

# Install dependencies
composer install

# Run Symfony commands
php bin/console cache:clear
php bin/console doctrine:migrations:migrate

# Run tests
php bin/phpunit
```

#### Infrastructure Management

```bash
# Start all services
cd infra
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all services
docker-compose down

# Rebuild containers
docker-compose up -d --build
```

## 🛠️ Available Scripts

See the `scripts/` directory for helper scripts that automate common tasks.

## 📝 Environment Configuration

Create `.env` files in the `backend/` directory as needed:
- `.env` - Base configuration
- `.env.local` - Local overrides (gitignored)

## 🗄️ Database

- **Type:** PostgreSQL 16
- **Default Database:** saas_fuel_db
- **Default User:** mauricio
- **Default Password:** secret
- **Port:** 5432

## 🔧 Services

### Services Running in Docker

1. **PostgreSQL Database** - Port 5432
2. **pgAdmin** - Port 8081
3. **Symfony PHP-FPM** - Internal
4. **Nginx** - Port 8000

## 📦 Monorepo Benefits

- **Unified version control** - Single repository for all related code
- **Atomic commits** - Changes to backend and infrastructure together
- **Shared tooling** - Common scripts and configurations
- **Easier collaboration** - All team members see the full stack

## 🤝 Contributing

1. Create a feature branch from `main`
2. Make your changes
3. Commit with clear messages
4. Push and create a pull request

## 📄 License

[Add your license information here]

