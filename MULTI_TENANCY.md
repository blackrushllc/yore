# Multi-Tenancy and Domain Resolution in Yore

## Overview

Yore Framework supports sophisticated multi-tenant applications through its flexible tenant resolution system. The new `TenantResolver` class provides multiple strategies for determining which tenant/domain should handle each request.

## How It Works

### Current Multi-Tenancy Structure

Yore uses a **domain-based multi-tenancy** approach where each tenant gets:

- **Separate Directory**: `/pages/_domains/{tenant}/`
- **Custom Configuration**: `modules.json` and `env.json` per tenant
- **Isolated Views**: Custom templates and themes per tenant
- **Module Overrides**: Tenant-specific module settings

### Tenant Resolution Strategies

The new system tries multiple resolution strategies in order of priority:

1. **Custom Resolvers** - User-defined logic (highest priority)
2. **Environment Variable** - `YORE_TENANT` env var
3. **HTTP Header** - `X-Tenant` header (useful for API calls)
4. **Subdomain Mapping** - Maps subdomains to tenants
5. **HTTP Host** - Standard domain-based resolution (current behavior)
6. **CLI Detection** - Handles command-line usage
7. **Default Fallback** - Falls back to 'local'

## Usage Examples

### Basic Usage

The system works automatically with no configuration needed:

```php
// In your application code
$currentTenant = TenantResolver::current(); // Returns current tenant
```

### Environment Variable Override

Perfect for development and testing:

```bash
# Set environment variable
export YORE_TENANT=development.local

# Or in your .env file
YORE_TENANT=staging.example.com
```

### Custom Tenant Resolution

Add your own tenant resolution logic:

```php
// In your bootstrap or configuration file
TenantResolver::addResolver(function() {
    // Custom logic example: tenant based on user session
    if (isset($_SESSION['tenant_override'])) {
        return $_SESSION['tenant_override'];
    }
    
    // Custom logic example: API key based tenant
    if (isset($_SERVER['HTTP_API_KEY'])) {
        return lookupTenantByApiKey($_SERVER['HTTP_API_KEY']);
    }
    
    return null; // Continue to next strategy
}, 200); // High priority
```

### Header-Based Tenant Selection

Useful for load balancers, proxies, or API calls:

```bash
curl -H "X-Tenant: api.example.com" https://yoursite.com/api/users
```

### CLI Usage

```bash
# Specify tenant for CLI commands
php web/cli.php --tenant=example.com migrate
```

### Subdomain Mapping

Configure subdomain to tenant mapping by modifying the `resolveFromSubdomain()` method in `TenantResolver.php`:

```php
$subdomainMap = [
    'app' => 'app.yoreweb.com',
    'api' => 'api.local',
    'admin' => 'admin.example.com',
    'staging' => 'staging.example.com',
];
```

## Configuration

### Enable Debug Logging

```php
TenantResolver::debug(true);
// Logs tenant resolution process to error_log
```

### Set Default Tenant

```php
TenantResolver::setDefault('my-default-tenant');
```

### Multiple Custom Resolvers

```php
// High priority resolver (runs first)
TenantResolver::addResolver(function() use ($database) {
    // Check database for tenant mapping
    return $database->getTenantByHost($_SERVER['HTTP_HOST']);
}, 300);

// Lower priority resolver
TenantResolver::addResolver(function() {
    // Fallback logic
    return 'fallback-tenant';
}, 100);
```

## Directory Structure

Each tenant has its own directory structure:

```
pages/
└── _domains/
    ├── example.com/           # Main production tenant
    │   ├── env.json          # Tenant-specific environment
    │   ├── modules.json      # Tenant-specific module config
    │   └── default/          # Site pages
    │       ├── home.json
    │       └── views/
    ├── staging.example.com/   # Staging tenant
    ├── api.example.com/       # API-only tenant
    └── local/                 # Development tenant (default)
```

## Benefits

### For Framework Users

1. **Flexible Development**: Easy to test different tenants locally
2. **Environment Separation**: Clean separation between dev/staging/prod
3. **Custom Logic**: Add your own tenant resolution logic
4. **API-Friendly**: Support for header-based tenant selection

### For Yore Framework

1. **Backward Compatible**: Existing domain-based resolution still works
2. **Extensible**: Easy to add new resolution strategies
3. **Performance**: Cached resolution prevents repeated lookups
4. **Debugging**: Optional debug logging for troubleshooting

## Migration from Old System

The new system is **completely backward compatible**. Your existing domain-based setup will continue to work exactly as before. The old line:

```php
$this->domain = $_SERVER['SERVER_NAME'] ?? 'local';
```

Is now replaced with:

```php
$this->domain = TenantResolver::current();
```

Which includes the HTTP host resolution as one of its strategies, so existing behavior is preserved while adding new capabilities.

## Use Cases

### Development Environment
```bash
export YORE_TENANT=local
composer serve
```

### Multi-Brand Application
```php
TenantResolver::addResolver(function() {
    $brand = $_SERVER['HTTP_X_BRAND'] ?? null;
    return $brand ? "{$brand}.local" : null;
});
```

### Database-Driven Tenants
```php
TenantResolver::addResolver(function() use ($database) {
    return $database->getTenantForHost($_SERVER['HTTP_HOST']);
});
```

### Load Balancer Integration
Configure your load balancer to set the `X-Tenant` header based on routing rules, allowing complex tenant resolution at the infrastructure level.
