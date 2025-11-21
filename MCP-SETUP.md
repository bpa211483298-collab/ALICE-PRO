# MCP Services Setup Guide

This guide will help you set up and run the MCP (Model Control Protocol) services locally.

## Prerequisites

1. **Docker Desktop**
   - Download and install from: https://www.docker.com/products/docker-desktop
   - Make sure Docker Desktop is running before proceeding

2. **Git** (for Windows users)
   - Download and install from: https://git-scm.com/download/win

## Setup Instructions

### 1. Start MCP Services

Open a terminal in the project root directory and run:

```bash
docker-compose up -d
```

This will start all the MCP services in detached mode.

### 2. Verify Services

To check if all services are running:

```bash
docker-compose ps
```

### 3. Test MCP Services

Run the test script to verify connectivity:

```bash
php test-mcp-local.php
```

### 4. Access Services

- **MCP Gateway**: http://localhost:8080
- **Git MCP**: http://localhost:8080/git/
- **Filesystem MCP**: http://localhost:8080/filesystem/
- **Terminal MCP**: http://localhost:8080/terminal/
- **Docker MCP**: http://localhost:8080/docker/
- **MinIO (S3)**: http://localhost:9001 (Username: minioadmin, Password: minioadmin)
- **PostgreSQL**: localhost:5432 (User: alice, Password: alice123, Database: alice_mcp)

## Troubleshooting

### Common Issues

1. **Docker not running**
   - Make sure Docker Desktop is running
   - Try restarting Docker Desktop

2. **Port conflicts**
   - If you get port conflict errors, check which application is using the port and stop it, or modify the ports in `docker-compose.yml`

3. **Service not responding**
   - Check logs: `docker-compose logs [service-name]`
   - Example: `docker-compose logs git-mcp`

### View Logs

View logs for all services:

```bash
docker-compose logs -f
```

View logs for a specific service:

```bash
docker-compose logs -f [service-name]
```

## Stopping Services

To stop all services:

```bash
docker-compose down
```

To stop and remove all containers, networks, and volumes:

```bash
docker-compose down -v
```
## Development

### Adding New Services

1. Add a new service to `docker-compose.yml`
2. Update the Nginx configuration in `docker/nginx/mcp-gateway.conf`
3. Update the test script `test-mcp-local.php`

### Environment Variables

You can override default settings by creating a `.env` file in the project root. Example:

```env
# MCP Configuration
MCP_DEFAULT_SERVER=local
MCP_LOCAL_URL=http://localhost:8080

# Database
POSTGRES_USER=alice
POSTGRES_PASSWORD=alice123
POSTGRES_DB=alice_mcp

# MinIO
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin
```

## License

This project is part of the ALICE Pro platform.
