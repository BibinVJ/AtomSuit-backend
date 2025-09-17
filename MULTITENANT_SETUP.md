# Multi-Tenant Setup Guide

This Laravel application has been configured for multi-tenancy with subdomain-based tenant identification and separate databases for each tenant.

## Architecture Overview

### Central Database
- **Purpose**: Stores tenants, domains, central users (super admins), OAuth tokens, and system-wide data
- **Tables**: `tenants`, `domains`, `users` (central), `oauth_*`, `webhook_logs`, etc.
- **Connection**: `central`

### Tenant Databases
- **Purpose**: Stores tenant-specific business data (users, items, sales, purchases, etc.)
- **Tables**: `users`, `items`, `categories`, `sales`, `purchases`, etc.
- **Connection**: Dynamic per tenant
- **Naming**: `{prefix}_{tenant_id}` (e.g., `atom_suit_tenant_default`)

## Configuration

### Environment Variables

Copy `.env.example.multitenant` to `.env` and configure:

```bash
# Central Database
DB_CONNECTION=mysql
DB_DATABASE=atom_suit_central

CENTRAL_DB_CONNECTION=mysql
CENTRAL_DB_DATABASE=atom_suit_central

# Tenant Configuration
TENANT_DB_PREFIX=atom_suit_tenant_
CENTRAL_DOMAIN=localhost

# Passport
PASSPORT_CONNECTION=central
```

### Database Setup

1. **Create Central Database**:
   ```sql
   CREATE DATABASE atom_suit_central;
   ```

2. **Run Multi-Tenant Setup**:
   ```bash
   php artisan setup:multitenant --fresh
   ```

   This command will:
   - Setup central database with migrations
   - Configure Passport
   - Create super admin user
   - Create default tenant
   - Setup tenant databases

## Usage

### Central Application (Super Admin)

- **Domain**: `localhost` (or your configured central domain)
- **Login**: `admin@admin.com` / `password`
- **Routes**: `/api/admin/*`

Available endpoints:
```
POST /api/admin/auth/login
POST /api/admin/auth/register
GET  /api/admin/profile
GET  /api/admin/tenants
POST /api/admin/tenants
```

### Tenant Application

- **Domain**: `{tenant}.localhost` (e.g., `default.localhost`)
- **Login**: `admin@{tenant_id}.com` / `password`
- **Routes**: `/api/*`

Available endpoints:
```
POST /api/login
POST /api/register
GET  /api/dashboard
GET  /api/items
POST /api/items
... (all business endpoints)
```

## Management Commands

### Create New Tenant
```bash
php artisan tenant:create "Company Name" "company.localhost" "admin@company.com"
```

### Migrate Tenants
```bash
php artisan tenants:migrate
```

### Seed Tenants
```bash
php artisan tenants:seed
```

### Central Database Operations
```bash
php artisan migrate --database=central
php artisan db:seed --database=central --class=CentralDatabaseSeeder
```

## Authentication & Authorization

### Guards
- `api`: Tenant users (default)
- `central`: Central users (super admins)

### Scopes
- `tenant-access`: Access tenant-specific data (default)
- `central-access`: Access central application

### Usage in Controllers
```php
// Tenant context
Route::middleware(['auth:api'])->group(function () {
    // Tenant-specific routes
});

// Central context
Route::middleware(['auth:central'])->group(function () {
    // Central admin routes
});
```

## Model Structure

### Central Models
- `CentralUser`: Super admin users
- `Tenant`: Tenant information
- `Domain`: Tenant domains

### Tenant Models
- `User`: Tenant users (with `BelongsToTenant` trait)
- `Item`, `Category`, `Sale`, etc.: Business models (with `BelongsToTenant` trait)

## File Structure

```
app/
├── Models/
│   ├── CentralUser.php          # Central admin users
│   ├── Tenant.php               # Tenant model
│   ├── User.php                 # Tenant users
│   └── ...                      # Other tenant models
├── Http/Controllers/
│   ├── Auth/
│   │   ├── CentralAuthController.php  # Central auth
│   │   └── AuthController.php         # Tenant auth
│   ├── TenantController.php     # Tenant management
│   └── ...                      # Tenant business controllers
└── Console/Commands/
    ├── CreateTenantCommand.php
    └── SetupMultiTenantCommand.php

database/
├── migrations/                  # Central database migrations
└── migrations/tenant/           # Tenant database migrations

routes/
├── api.php                      # Central routes
└── tenant.php                   # Tenant routes
```

## Troubleshooting

### Common Issues

1. **Tenant not found**: Ensure domain is registered and pointing to correct tenant
2. **Database connection issues**: Check `.env` configuration for central and tenant databases
3. **Authentication issues**: Verify guard configuration and token scopes

### Useful Commands

```bash
# Check tenant status
php artisan tenants:list

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Regenerate Passport keys
php artisan passport:keys --force
```

## Development

### Adding New Features

1. **Tenant-specific**: Add to tenant migrations/models with `BelongsToTenant` trait
2. **Central features**: Add to central migrations/models without tenant trait

### Testing Different Tenants

Add entries to your hosts file:
```
127.0.0.1 default.localhost
127.0.0.1 company.localhost
127.0.0.1 client.localhost
```

## Security Considerations

- Tenant isolation is enforced at the database level
- Central users cannot access tenant data directly
- Tenant users cannot access central application
- All tenant data is scoped automatically via `BelongsToTenant` trait