# Yore Firewall System

A flexible, hierarchical firewall system for controlling page access with multiple configurable rules. Supports multi-tenancy, caching, and comprehensive debugging capabilities.

## Table of Contents
- [Overview](#overview)
- [Configuration](#configuration)
- [Built-in Firewalls](#built-in-firewalls)
- [Hierarchical Configuration](#hierarchical-configuration)
- [Development vs Production](#development-vs-production)
- [Debug Mode](#debug-mode)
- [Cache Management](#cache-management)
- [Custom Firewalls](#custom-firewalls)
- [CLI Commands](#cli-commands)
- [Examples](#examples)

## Overview

The Yore firewall system provides:
- **Hierarchical Configuration**: Rules cascade from global → domain → site → page
- **Multiple Firewall Types**: IP, Authentication, Role-based, Entity ownership
- **Automatic Environment Detection**: Disables caching in development mode
- **Comprehensive Debugging**: Page-level debug with detailed execution results
- **Multi-tenant Support**: Tenant-specific firewall configurations
- **Performance Optimized**: Smart caching with CLI-based management

## Configuration

### Basic Usage

Initialize the firewall system in your application:

```php
use App\Firewall;

// Auto-detects environment and sets appropriate cache behavior
Firewall::setCaching(true);

// Check page access
$result = Firewall::check($pageFirewalls, $pageData, $tenant);

if (!$result->allowed) {
    // Handle access denied
    header('HTTP/1.1 403 Forbidden');
    echo $result->message;
    exit;
}
```

### Page-Level Configuration

Add firewalls directly to your page JSON:

```json
{
  "title": "Admin Dashboard",
  "debug_firewall": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin", "super_admin"]
    }
  ]
}
```

## Built-in Firewalls

### 1. Authentication Firewall

Controls access based on user login status.

```json
{
  "type": "auth",
  "required": true,
  "redirect_if_logged_in": "/dashboard"
}
```

**Options:**
- `required` (bool): Whether authentication is required (default: true)
- `redirect_if_logged_in` (string): URL to redirect authenticated users

### 2. Role-based Firewall

Restricts access to specific user roles.

```json
{
  "type": "role",
  "roles": ["admin", "editor", "moderator"]
}
```

**Options:**
- `roles` (array): List of allowed roles

### 3. IP Firewall

Controls access based on client IP address with CIDR support.

```json
{
  "type": "ip",
  "mode": "whitelist",
  "ips": ["192.168.1.0/24", "10.0.0.100", "203.0.113.0/24"]
}
```

**Options:**
- `mode` (string): "whitelist" or "blacklist" (default: whitelist)
- `ips` (array): List of IP addresses or CIDR ranges

### 4. Entity Ownership Firewall

Ensures users can only access entities they own. Works with page data that contains ownership information.

```json
{
  "type": "entity",
  "field": "created_by_user_id"
}
```

**Options:**
- `field` (string): Page data field containing owner ID (default: "created_by_user_id")

**How it works:**
- Checks the specified field in the page data against the current user's ID
- Requires the page data to contain the ownership information
- Uses strict numeric comparison for security
- Falls back to access denied if ownership cannot be verified

**Page Data Requirements:**
The page data must include the ownership field. This typically comes from:
- Page JSON files that include ownership data
- Database queries that populate page data with ownership information
- API responses that include entity ownership details

**Example Page Data:**
```json
{
  "title": "My Article",
  "content": "Article content...",
  "created_by_user_id": 123,
  "firewalls": [
    {
      "type": "entity",
      "field": "created_by_user_id"
    }
  ]
}
```

### 5. Public Access

Always allows access (useful for overriding inherited restrictions).

```json
{
  "type": "public"
}
```

## Hierarchical Configuration

Firewall rules cascade from most general to most specific:

1. **Global**: `../pages/firewalls.json`
2. **Domain**: `../pages/_domains/{domain}/firewalls.json`
3. **Site**: `../pages/_domains/{domain}/{site}/firewalls.json`
4. **Page Directory**: `../pages/{path}/firewalls.json`
5. **Page JSON**: Individual page firewall rules

### Example Hierarchy

```
pages/
├── firewalls.json                           # Global rules
├── _domains/
│   └── client.com/
│       ├── firewalls.json                   # Domain rules
│       └── admin/
│           ├── firewalls.json               # Site rules
│           └── users/
│               ├── firewalls.json           # Directory rules
│               └── edit.json                # Page rules
```

### Inheritance Control

Control rule inheritance with the `inherit` property:

```json
{
  "inherit": false,
  "firewalls": [
    {
      "type": "public"
    }
  ]
}
```

When `inherit: false`, only the current level's rules apply (no cascading).

## Development vs Production

### Development Mode (Auto-detected)

The system automatically detects development environments and:
- **Disables caching** for live config changes
- **Enhanced error reporting** with exceptions
- **Immediate feedback** on configuration changes

**Detection criteria:**
- Debug constants (`YORE_DEBUG`, `APP_DEBUG`, `DEBUG`)
- Environment variables (`APP_ENV=development`)
- Localhost domains (`localhost`, `127.0.0.1`, `*.local`)
- Development markers (`composer.json`, `.env.development`)

### Production Mode

- **Caching enabled** by default for performance
- **Silent error handling** to prevent information disclosure
- **CLI-based cache management** for deployment control

### Manual Override

```php
// Force production mode (enable caching)
Firewall::setCaching(true, '../storage/cache/firewalls', false);

// Force development mode (disable caching)
Firewall::setCaching(false, null, true);
```

## Debug Mode

### Automatic Debug Output

Debug information automatically appears when you call `Firewall::check()` if either:

1. **Page JSON** contains `"debug_firewall": true`
2. **Global debug mode** enabled via `Firewall::setDebugMode(true)`

```json
{
  "title": "My Page",
  "debug_firewall": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    }
  ]
}
```

### How It Works

When you call `Firewall::check()` in your application, debug output automatically appears at the end of the page if debugging is enabled. No additional setup required.

```php
use App\Firewall;

// Debug output happens automatically when debug_firewall: true
$result = Firewall::check($pageFirewalls, $pageData, $tenant);

if (!$result->allowed) {
    header('HTTP/1.1 403 Forbidden');
    echo $result->message;
    exit;
}
```

### Debug Information Shows

- ✅ **Environment Detection**: Development vs Production mode
- 📁 **Configuration Files**: All firewall files in hierarchy order  
- 🛡️ **Applied Rules**: Which firewalls are active and from where
- 🔍 **Execution Results**: Detailed pass/fail information for each firewall
- 📊 **Cache Statistics**: Memory and file cache status

### Example Debug Output

```
🔒 Firewall Debug Information

Environment: 🔧 Development | Cache: ❌ Disabled | Tenant: default
Page: client.com / admin / dashboard

Configuration Files (hierarchy order):
📄 ../pages/firewalls.json (cached)
❌ ../pages/_domains/client.com/firewalls.json
📄 ../pages/_domains/client.com/admin/firewalls.json [inherit: false]
  └─ auth
  └─ role

Applied Firewalls (from hierarchy):
🛡️ auth
🛡️ role

Page-Specific Firewalls:
🔐 ip

Execution Results:
✅ Allowed auth - User logged in as admin
✅ Allowed role - User has admin role (required: admin, editor)
✅ Allowed ip - Client IP 192.168.1.100 matches whitelist

Cache Stats: Memory: 3 entries, File: 0 entries, Size: 0 KB
```

## Cache Management

### CLI Commands

Use the Yore CLI tool for cache management:

```bash
# Show cache statistics
./yore firewall_cache --stats

# Clear all cache
./yore firewall_cache --clear-all

# Clear specific file cache
./yore firewall_cache --clear-path="../pages/_domains/client.com/firewalls.json"

# Enable/disable caching
./yore firewall_cache --enable --cache-dir="../storage/cache/firewalls"
./yore firewall_cache --disable

# Show help
./yore firewall_cache --help
```

### Programmatic Cache Control

```php
// Clear all cache
Firewall::clearCache();

// Clear specific file cache
Firewall::clearCache('../pages/_domains/client.com/firewalls.json');

// Get cache statistics
$stats = Firewall::getCacheStats();

// Warm cache for domain
$results = Firewall::warmCache('client.com');
```

## Custom Firewalls

### Creating Custom Firewalls

Implement the `FirewallInterface`:

```php
use App\FirewallInterface;
use App\FirewallResult;

class CustomFirewall implements FirewallInterface
{
    public function check(array $config, array $pageData = []): FirewallResult
    {
        // Your custom logic here
        $allowed = $this->customCheck($config, $pageData);
        
        return new FirewallResult(
            $allowed,
            $allowed ? null : 'Custom check failed',
            ['custom_data' => 'value']
        );
    }
    
    private function customCheck(array $config, array $pageData): bool
    {
        // Implement your firewall logic
        return true;
    }
}
```

### Registering Custom Firewalls

```php
// Global registration
Firewall::register('custom', CustomFirewall::class);

// Tenant-specific registration
Firewall::register('custom', CustomFirewall::class, 'tenant-name');
```

### Using Custom Firewalls

```json
{
  "type": "custom",
  "custom_option": "value",
  "another_option": true
}
```

## CLI Commands

The system integrates with your existing CLI infrastructure:

### Available Commands

```bash
# Firewall cache management
./yore firewall_cache --help

# Show current status
./yore firewall_cache --stats

# Production deployment workflow
./yore firewall_cache --clear-all
```

### Integration with Deployment

```bash
#!/bin/bash
# deployment script

# Clear old cache
./yore firewall_cache --clear-all

# Verify cache status
./yore firewall_cache --stats
```

## Examples

### Multi-level Security

**Global rules** (`../pages/firewalls.json`):
```json
{
  "firewalls": [
    {
      "type": "ip",
      "mode": "blacklist", 
      "ips": ["192.168.1.100"]
    }
  ]
}
```

**Admin section** (`../pages/_domains/client.com/admin/firewalls.json`):
```json
{
  "firewalls": [
    {
      "type": "auth",
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin", "super_admin"]
    }
  ]
}
```

**Sensitive page** (`../pages/_domains/client.com/admin/users/edit.json`):
```json
{
  "title": "Edit User",
  "debug_firewall": true,
  "firewalls": [
    {
      "type": "entity",
      "field": "created_by_user_id"
    }
  ]
}
```

### Development Workflow

1. **Enable debug mode** in page JSON:
   ```json
   {"debug_firewall": true}
   ```

2. **Development auto-detection** disables caching

3. **Make configuration changes** - they take effect immediately

4. **Test access patterns** with detailed debug output

5. **Deploy to production** with cache management:
   ```bash
   ./yore firewall_cache --clear-all
   ./yore firewall_cache --enable
   ```

### Multi-tenant Configuration

```php
// Register tenant-specific firewall
Firewall::register('tenant_check', TenantFirewall::class, 'client-a');

// Check with tenant context
$result = Firewall::check($firewalls, $pageData, 'client-a');
```

**Tenant-specific config** (`../pages/_domains/client-a.com/firewalls.json`):
```json
{
  "firewalls": [
    {
      "type": "tenant_check",
      "tenant_id": "client-a",
      "allowed_features": ["feature1", "feature2"]
    }
  ]
}
```

## Best Practices

1. **Use hierarchy effectively**: Place common rules at higher levels
2. **Enable debug in development**: Add `"debug_firewall": true` to pages during development
3. **Test thoroughly**: Use debug mode to verify rule application
4. **Cache management**: Clear cache after configuration changes in production
5. **Security layering**: Combine multiple firewall types for defense in depth
6. **Performance**: Let the system auto-detect environment for optimal caching behavior

## Troubleshooting

### Common Issues

**Rules not applying:**
- Check debug output to see which files are loaded
- Verify JSON syntax in configuration files
- Ensure correct file hierarchy and naming

**Performance issues:**
- Verify caching is enabled in production
- Use CLI tools to warm cache after deployments
- Check cache statistics for effectiveness

**Debug not showing:**
- Ensure `debug_firewall: true` in page JSON
- Check admin user permissions in production mode
- Verify development mode detection is working

**Cache not clearing:**
- Use CLI commands rather than manual file deletion
- Check file permissions on cache directory
- Verify cache directory path configuration
