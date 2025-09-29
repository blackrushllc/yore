# Yore Database System Guide

## Overview

Yore includes a powerful, multi-tenant database system built on PDO (PHP Data Objects) with MySQL support. The database module provides seamless integration with Yore's domain-based architecture, template system, and modular framework.

## Architecture

### Core Components

- **Database Module** (`modules/Database/Module.php`) - Main database connectivity and operations
- **PDO-based** - Secure prepared statements and modern PHP database practices
- **Multi-tenant Support** - Different database configurations per domain
- **Fred Integration** - Direct database access in templates
- **File Storage** - Binary file storage capabilities within the database

### Key Features

- Lazy connection initialization (connects only when first used)
- Automatic error handling with framework integration
- Template engine integration for dynamic content
- DataTables integration for automatic table formatting
- Binary file upload/download system
- Cross-domain configuration inheritance

## Configuration

### Environment Configuration

Yore uses a hierarchical configuration system for database settings:

**Priority Order:**
1. Domain-specific settings in `pages/_domains/{domain}/env.json`
2. Global fallback defaults in the Database module

### Configuration Parameters

Create or update your `env.json` file with database settings:

```json
{
  "database_module_host": "localhost",
  "database_module_db": "yore_app",
  "database_module_username": "yore_user",
  "database_module_password": "secure_password",
  "database_module_port": "3306",
  "database_module_charset": "utf8mb4"
}
```

### Local Development Setup

#### 1. Install MySQL/MariaDB

**macOS (using Homebrew):**
```bash
brew install mysql
brew services start mysql
```

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

**Windows:**
Download and install MySQL from the official website or use XAMPP/WAMP.

#### 2. Create Database and User

```sql
-- Connect to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE yore_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user for local development
CREATE USER 'yore_dev'@'localhost' IDENTIFIED BY 'dev_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON yore_dev.* TO 'yore_dev'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. Configure Local Environment

Create `pages/_domains/local/env.json`:

```json
{
  "database_module_host": "localhost",
  "database_module_db": "yore_dev",
  "database_module_username": "yore_dev",
  "database_module_password": "dev_password",
  "database_module_port": "3306",
  "database_module_charset": "utf8mb4"
}
```

#### 4. Run Migrations

```bash
# From project root
./web/yore cli migrate

# Or directly with PHP
php web/cli.php migrate
```

### Production Setup

#### 1. Database Server Configuration

**Security Settings:**
- Use strong passwords
- Limit database user privileges
- Enable SSL connections
- Configure firewall rules
- Regular backups

```sql
-- Create production database
CREATE DATABASE yore_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create production user with limited privileges
CREATE USER 'yore_prod'@'%' IDENTIFIED BY 'VERY_SECURE_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON yore_prod.* TO 'yore_prod'@'%';
FLUSH PRIVILEGES;
```

#### 2. Production Environment Configuration

Create `pages/_domains/yourdomain.com/env.json`:

```json
{
  "database_module_host": "your-db-server.com",
  "database_module_db": "yore_prod",
  "database_module_username": "yore_prod",
  "database_module_password": "VERY_SECURE_PASSWORD",
  "database_module_port": "3306",
  "database_module_charset": "utf8mb4"
}
```

#### 3. Environment Variables (Alternative)

For enhanced security, use environment variables:

```bash
# Set in your server environment
export YORE_DB_HOST="your-db-server.com"
export YORE_DB_NAME="yore_prod"
export YORE_DB_USER="yore_prod"
export YORE_DB_PASS="VERY_SECURE_PASSWORD"
```

Then in your `env.json`:
```json
{
  "database_module_host": "${YORE_DB_HOST}",
  "database_module_db": "${YORE_DB_NAME}",
  "database_module_username": "${YORE_DB_USER}",
  "database_module_password": "${YORE_DB_PASS}"
}
```

## Usage

### Basic Database Operations

#### In Modules

```php
class MyModule extends Modules {
    public function someMethod() {
        // Access database through controller
        $db = $this->controller->database;
        
        // Execute query with parameters
        $stmt = $db->sql("SELECT * FROM users WHERE active = ?", [1]);
        $users = $stmt->fetchAll();
        
        // Insert data
        $db->sql("INSERT INTO users (name, email) VALUES (?, ?)", 
                 ['John Doe', 'john@example.com']);
        
        // Get last inserted ID
        $lastId = $db->pdo->lastInsertId();
    }
}
```

#### In Controllers

```php
// Access database module
$database = $this->modules['Database'];

// Or use the shortcut property
$database = $this->database;

// Execute queries
$stmt = $database->sql("SELECT * FROM products WHERE category_id = ?", [$categoryId]);
$products = $stmt->fetchAll();
```

### Template Integration (Fred Functions)

Yore's template system provides direct database access:

#### Display Data Tables

```html
<!-- In your .html or .blade.php views -->

<!-- Browse entire table with DataTables integration -->
@fred_browse('products')

<!-- Custom query with formatted table -->
@fred_select('SELECT name, price, category FROM products WHERE active = 1')

<!-- Generate select options -->
<select name="category">
    @fred_options('SELECT id, name FROM categories ORDER BY name')
</select>

<!-- Generate select with pre-selected option -->
<select name="category">
    @fred_options(['SELECT id, name FROM categories ORDER BY name', $selected_category_id])
</select>
```

#### Fetch Single Records

```html
<!-- Fetch data for use in template -->
@if(fred_fetch('SELECT * FROM user_profile WHERE user_id = ' . $_SESSION['user_id']))
    <h1>Welcome, @fred_field('first_name')!</h1>
    <p>Email: @fred_field('email')</p>
    <p>Last Login: @fred_field('last_login')</p>
@endif
```

### File Storage System

Yore includes a binary file storage system within the database:

```php
// Upload file to database
$database->uploadFileToDatabase($_FILES['upload'], $code, $reference);

// Retrieve file from database
$filePath = $database->retrieveFileFromDatabase($fileId, $code, $downloadDirectory);
```

## Migration System

### Running Migrations

```bash
# Standard migration command
./web/yore cli migrate

# Alternative methods
cd web && ./yore cli migrate
php web/cli.php migrate
```

### Creating Custom Migrations

Create migration files in your modules or use the CLI system to manage database schema changes.

## Multi-Tenant Database Configuration

### Per-Domain Databases

Each domain can have its own database configuration:

```
pages/
└── _domains/
    ├── app.example.com/
    │   └── env.json          # App-specific database
    ├── api.example.com/
    │   └── env.json          # API-specific database
    └── admin.example.com/
        └── env.json          # Admin-specific database
```

### Shared Database with Tenant Isolation

Use a single database with tenant prefixes or tenant ID columns:

```json
{
  "database_module_host": "shared-db.com",
  "database_module_db": "multi_tenant_app",
  "database_module_username": "app_user",
  "database_module_password": "password",
  "tenant_id": "client_123"
}
```

## Performance Optimization

### Connection Pooling

The database module uses lazy loading - connections are only established when first used:

```php
// Connection is created only when sql() is first called
$stmt = $database->sql("SELECT * FROM users");
```

### Query Optimization

```php
// Use prepared statements (automatically handled)
$stmt = $database->sql("SELECT * FROM products WHERE category = ? AND price > ?", 
                       [$category, $minPrice]);

// Avoid N+1 queries - fetch related data in single query
$stmt = $database->sql("
    SELECT p.*, c.name as category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.active = 1
");
```

### Indexing

Ensure proper database indexes for your queries:

```sql
-- Add indexes for commonly queried columns
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_products_active_price ON products(active, price);
```

## Security Best Practices

### Database Security

1. **Use Strong Passwords**: Generate cryptographically secure passwords
2. **Limit Privileges**: Grant only necessary permissions to database users
3. **Enable SSL**: Use encrypted connections in production
4. **Regular Updates**: Keep MySQL/MariaDB updated
5. **Backup Strategy**: Implement automated backups with testing

### Application Security

1. **Prepared Statements**: Always use parameterized queries (handled automatically)
2. **Input Validation**: Validate data before database operations
3. **Error Handling**: Don't expose database errors to users
4. **Access Control**: Implement proper authentication and authorization

```php
// Good - using parameters
$stmt = $database->sql("SELECT * FROM users WHERE id = ?", [$userId]);

// Bad - SQL injection risk (don't do this)
$stmt = $database->sql("SELECT * FROM users WHERE id = " . $userId);
```

## Troubleshooting

### Common Issues

#### Connection Failures

**Problem**: "Connection refused" or "Access denied"

**Solutions:**
- Verify database server is running
- Check host, port, username, and password
- Ensure database user has proper privileges
- Check firewall settings

```bash
# Test connection manually
mysql -h localhost -u yore_user -p yore_db
```

#### Configuration Issues

**Problem**: Database settings not loading

**Solutions:**
- Verify `env.json` file exists and has correct syntax
- Check file permissions
- Ensure correct domain directory structure
- Use TenantResolver debugging to verify domain resolution

```php
// Enable debug mode to see configuration loading
TenantResolver::debug(true);
```

#### Performance Issues

**Problem**: Slow database queries

**Solutions:**
- Enable MySQL slow query log
- Add appropriate indexes
- Optimize queries with EXPLAIN
- Consider connection pooling for high-traffic applications

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Analyze query performance
EXPLAIN SELECT * FROM products WHERE category_id = 1;
```

### Debug Mode

Enable debug mode to see database operations:

```php
// In your domain's env.json
{
  "debug": true,
  "database_module_host": "localhost"
  // ... other settings
}
```

## Tips and Tricks

### 1. Environment-Specific Configurations

Use different database configurations for different environments:

```bash
# Development
export YORE_TENANT=local
composer serve

# Staging
export YORE_TENANT=staging.example.com
composer serve

# Production uses domain-based resolution automatically
```

### 2. Database Seeding

Create seed data for development:

```php
// In a module's init method
public function yore_module_init($controller, $key) {
    if ($controller->is_debug) {
        $this->seedDevelopmentData();
    }
}

private function seedDevelopmentData() {
    $db = $this->controller->database;
    
    // Check if data already exists
    $stmt = $db->sql("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Insert sample data
        $db->sql("INSERT INTO users (name, email) VALUES (?, ?)", 
                 ['Test User', 'test@example.com']);
    }
}
```

### 3. Database Backup Automation

```bash
#!/bin/bash
# backup-database.sh

DB_NAME="yore_prod"
DB_USER="yore_prod"
BACKUP_DIR="/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup
mysqldump -u $DB_USER -p $DB_NAME > $BACKUP_DIR/yore_backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/yore_backup_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "yore_backup_*.sql.gz" -mtime +30 -delete
```

### 4. Development vs Production Databases

Keep development and production databases completely separate:

```json
// pages/_domains/local/env.json (development)
{
  "database_module_host": "localhost",
  "database_module_db": "yore_dev",
  "database_module_username": "dev_user",
  "database_module_password": "dev_password"
}

// pages/_domains/yourdomain.com/env.json (production)
{
  "database_module_host": "prod-db-server.com",
  "database_module_db": "yore_prod", 
  "database_module_username": "prod_user",
  "database_module_password": "very_secure_production_password"
}
```

## Gotchas and Common Pitfalls

### 1. Domain Resolution Issues

**Problem**: Database connecting to wrong configuration
**Solution**: Use `TenantResolver::debug(true)` to verify domain resolution

### 2. Connection Timing

**Problem**: Database connection established too early
**Solution**: The module uses lazy loading - connections are made only when needed

### 3. File Permissions

**Problem**: Cannot read `env.json` configuration files
**Solution**: Ensure proper file permissions:
```bash
chmod 644 pages/_domains/*/env.json
```

### 4. Character Set Issues

**Problem**: Emoji or special characters not displaying correctly
**Solution**: Always use `utf8mb4` character set:
```json
{
  "database_module_charset": "utf8mb4"
}
```

---

The Yore database system provides a robust, secure, and flexible foundation for building multi-tenant applications with seamless template integration and comprehensive development tools.
