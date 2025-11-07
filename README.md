# SaaS Fuel API

Backend API built with Symfony for a multi-tenant fuel delivery SaaS platform.
Provides secure authentication, company-scoped data access, and resources for managing clients, orders, trucks, and users.

## 📁 Project Structure

```
saas_fuel_api/
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


## Prerequisites

Make sure you have the following installed on your machine:

- **Docker**
- **Docker Compose**
- **Git**

---

## Clone the Repository

```bash
git clone https://github.com/mmauriciobastos/saas_fuel_api.git
```

Navigate into the project directory:

```bash
cd saas_fuel_api
```

---

## Start the Docker Environment

The Docker setup is located inside the `infra` folder:

```bash
cd infra
docker-compose up -d
```

This will build and start all required containers.

---

## Install Backend Dependencies

Navigate to the backend (Symfony API) folder:

```bash
cd ..
cd backend
```

Install PHP dependencies inside the container:

```bash
docker exec -it symfony-app composer install
```

---

## Database Setup

Generate and run migrations:

```bash
docker exec -it symfony-app php bin/console doctrine:migrations:diff
docker exec -it symfony-app php bin/console doctrine:migrations:migrate
```

Seed the database with fixtures:

```bash
docker exec -it symfony-app php bin/console doctrine:fixtures:load
```

### API (Swagger UI)
Open in your browser:

```
http://localhost:8000/api/docs
```

### pgAdmin
```
http://localhost:8081
```

**pgAdmin Credentials:**

| Field      | Value             |
|-----------|-------------------|
| Login     | admin@local.com   |
| Password  | admin             |


## Frontend (Admin Panel - Next.js)

⚠️ **Important:** 

> The frontend administration panel is located in a **separate repository**:  
> https://github.com/mmauriciobastos/saas_fuel_admin  
> Make sure to **clone it outside** of the current Symfony API project folder.

Recomended folder structure:
```
your_local_folder/
├── saas_fuel_api/    # Symfony API (backend)
└── saas_fuel_admin/  # NextJS Admin Dashboard (frontend)
```

### Clone the Frontend Repository

Clone it outside the `saas_fuel_api` folder (or anywhere you prefer):

```
git clone https://github.com/mmauriciobastos/saas_fuel_admin.git
```

### Start the Frontend Application

Navigate to the frontend project and start the containers:

```
cd saas_fuel_admin
docker-compose up -d
```

This will build and run the Next.js Admin Dashboard application.

### Access the Admin Dashboard (frontend)

http://localhost:3000


Frontend repository for reference:
```
https://github.com/mmauriciobastos/saas_fuel_admin
```

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



