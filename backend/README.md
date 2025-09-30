# ESG Backend API

This is a CodeIgniter 4 backend API for the ESG (Environmental, Social, and Governance) project.

## Features

- RESTful API endpoints
- Database migrations
- CORS support
- MySQL database integration
- Docker containerization

## API Endpoints

### Companies
- GET `/api/companies` - Get all companies
- GET `/api/companies/{id}` - Get specific company
- POST `/api/companies` - Create new company
- PUT `/api/companies/{id}` - Update company
- DELETE `/api/companies/{id}` - Delete company

## Environment Configuration

The application uses the following environment variables:

- `CI_ENVIRONMENT`: Environment mode (development/production)
- `database.default.hostname`: Database host (default: mysql)
- `database.default.database`: Database name (default: esg_db)
- `database.default.username`: Database username (default: root)
- `database.default.password`: Database password (default: root)

## Database Schema

### Companies Table
- `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
- `name` (VARCHAR(255), NOT NULL)
- `email` (VARCHAR(255), NOT NULL)
- `phone` (VARCHAR(50), NULLABLE)
- `address` (TEXT, NULLABLE)
- `created_at` (DATETIME, NOT NULL)
- `updated_at` (DATETIME, NOT NULL)

## Development

### Running with Docker Compose

From the root directory:

```bash
docker-compose up -d
```

### Database Migrations

Run migrations using CI4 spark command:

```bash
php spark migrate
```

### API Testing

You can test the API endpoints using tools like Postman or curl:

```bash
# Get all companies
curl http://localhost:9218/api/companies

# Create a new company
curl -X POST http://localhost:9218/api/companies \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "name=Test Company&email=test@example.com&phone=123456789"
```