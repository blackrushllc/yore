# Roles and Permissions in Yore Framework

## Overview

The Yore framework provides a comprehensive role-based access control system that operates at multiple levels:

1. **Session-based Roles** - Core role management through `$_SESSION['role']`
2. **View-based Role Routing** - Automatic view selection based on user roles
3. **Firewall Permission System** - Flexible, configurable access control for pages and resources

> **Note:** For detailed firewall configuration and advanced permission patterns, see [FIREWALL.md](FIREWALL.md). For multi-tenant role management, see [MULTI_TENANCY.md](MULTI_TENANCY.md).

## Core Role System

### How Roles Work

Roles in Yore are stored in the user session (`$_SESSION['role']`) and are set during the authentication process through various modules.

#### Available Roles

The framework recognizes these standard roles based on the actual implementation:

**Core Roles (from Users module):**
- `admin` - Full administrative access (from settings or database)
- `user` - Regular authenticated user (default role)
- `readonly` - Read-only access (from settings)

**Additional Roles (from codebase):**
- `super_admin` - Super administrator (highest level, used in firewall)
- `moderator` - Content moderation privileges (used in firewall)
- `guest` - Unauthenticated users (fallback role)

**Development Roles (Admin module):**
- Hardcoded `admin` role for username 'admin'
- Hardcoded `user` role for username 'erik'

### Role Assignment

Roles are assigned during login through multiple methods in the Users and Admin modules:

#### Users Module Role Assignment

The Users module supports multiple authentication methods:

1. **Settings-based authentication** (development):
```php
// Admin role from settings
$_SESSION['role'] = $this->role = 'admin';

// User role from settings  
$_SESSION['role'] = $this->role = 'user';

// Readonly role from settings
$_SESSION['role'] = $this->role = 'readonly';
```

2. **Database-based authentication**:
```php
// Role from database user record
$_SESSION['role'] = $this->role = $this->user['role'] ?? 'user';
```

#### Admin Module Role Assignment

The Admin module uses hardcoded credentials for development:

```php
// Hardcoded admin role
if ($username == 'admin' && $password == 'mermaid') {
    $_SESSION['role'] = $this->role = 'admin';
}

// Hardcoded user role
if ($username == 'erik' && $password == 'mermaid') {
    $_SESSION['role'] = $this->role = 'user';
}
```

### Database Integration

Roles are stored in the users table:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255),
    password VARCHAR(255),
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Role-Based View Rendering

### Automatic View Selection

The Controller class automatically selects views based on the current user's role:

```php
// In pages/domain/site/views/
admin.html        // Shown to users with 'admin' role
user.html         // Shown to users with 'user' role  
moderator.blade.php // Blade template for 'moderator' role
```

### Page JSON Configuration

Configure role-based views in your page JSON:

```json
{
  "domain": "example.com",
  "site": "dashboard",
  "page": "home",
  "title": "Dashboard",
  "view": "default",
  "views": {
    "admin": "admin-dashboard",
    "user": "user-dashboard", 
    "moderator": "mod-dashboard"
  }
}
```

Or use an array format:
```json
{
  "views": ["admin", "user", "moderator"]
}
```

### View Resolution Priority

The Controller resolves views in this order:
1. **Role-specific Blade template** (e.g., `admin.blade.php`) - if role is in `views` array
2. **Role-specific HTML template** (e.g., `admin.html`) - if role is in `views` array
3. **Domain-specific Blade template** (e.g., `default.blade.php`)
4. **Domain-specific HTML template** (e.g., `default.html`)
5. **Default site template**

**Note:** Role-based views are only checked if the user has a role set in `$_SESSION['role']` and the page configuration includes a `views` array with that role.

### View Resolution Implementation Details

The actual view resolution logic in `Controller.php` works as follows:

1. **Check for role-specific views** if `$_SESSION['role']` is set and page has `views` configuration
2. **Support two view formats:**
   - Object format: `{"admin": "admin-dashboard", "user": "user-dashboard"}`
   - Array format: `["admin", "user", "moderator"]`
3. **View file naming convention:**
   - HTML: `{role}.html` (e.g., `admin.html`)
   - Blade: `{role}.blade.php` (e.g., `admin.blade.php`)
4. **Path structure:**
   - Domain-specific: `../pages/_domains/{domain}/{site}/views/{role}.html`
   - Default: `../pages/{site}/views/{role}.html`

## Firewall Integration

The firewall system provides fine-grained access control that builds upon the existing role infrastructure.

### Basic Role Firewall Example

```json
{
  "firewalls": [
    {
      "type": "auth", 
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin"]
    }
  ]
}
```

> **For Complete Firewall Documentation:** See [FIREWALL.md](FIREWALL.md) for comprehensive examples of:
> - Multi-layer security combining roles with IP restrictions, time limits, and entity ownership
> - Custom firewall implementation
> - Advanced permission patterns
> - Security best practices

## Hierarchical Firewall Configuration

The firewall system now supports hierarchical `firewalls.json` files that follow Yore's JSON-first philosophy and leverage the existing directory structure.

### Directory-Based Security Rules

Instead of configuring security on every individual page, you can now create `firewalls.json` files at different levels of your directory structure:

```
pages/
├── firewalls.json                          # Global fallback rules
├── _domains/
│   └── client-a.com/
│       ├── firewalls.json                  # Domain-level rules
│       ├── admin/
│       │   ├── firewalls.json              # Site-level rules (all admin pages)
│       │   ├── dashboard.json              # Individual page
│       │   └── settings/
│       │       ├── firewalls.json          # Path-level rules
│       │       └── users.json              # Individual page
└── admin/
    ├── firewalls.json                      # Default domain admin rules
    └── dashboard.json
```

### Role-Based Security Hierarchy

#### Domain-Level Role Requirements
```json
// pages/_domains/client-a.com/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    }
  ]
}
```

#### Site-Level Role Restrictions
```json
// pages/_domains/client-a.com/admin/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "role",
      "roles": ["admin", "super_admin"]
    }
  ]
}
```

#### Path-Level Granular Control
```json
// pages/_domains/client-a.com/admin/settings/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "role",
      "roles": ["admin"]
    }
  ]
}
```

### Multi-Tenant Role Management

Each tenant can have completely different role hierarchies:

#### Enterprise Client (Strict Hierarchy)
```json
// pages/_domains/enterprise-client.com/admin/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin"]
    }
  ]
}
```

#### Small Business Client (Flexible Roles)
```json
// pages/_domains/small-biz.com/admin/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin", "manager", "owner"]
    }
  ]
}
```

### Inheritance Control for Roles

#### Override Parent Role Requirements
```json
// pages/_domains/client.com/reports/public/firewalls.json
{
  "inherit": false,
  "firewalls": [
    {
      "type": "public"
    }
  ]
}
```

This makes public reports accessible even if the parent `/reports/` directory requires authentication.

### Integration with Existing Role System

The hierarchical firewall system works seamlessly with the existing role-based view rendering:

```json
// Page configuration with role-based views
{
  "domain": "client.com",
  "site": "dashboard", 
  "page": "home",
  "view": "default",
  "views": {
    "admin": "admin-dashboard",
    "manager": "manager-dashboard",
    "user": "user-dashboard"
  }
}
```

Combined with hierarchical firewalls:
```json
// pages/_domains/client.com/dashboard/firewalls.json
{
  "inherit": true,
  "firewalls": [
    {
      "type": "auth",
      "required": true
    },
    {
      "type": "role",
      "roles": ["admin", "manager", "user"]
    }
  ]
}
```

## Module Integration

### Users Module

**Location:** `modules/Users/Module.php`

The Users module handles standard user authentication and role assignment:

```php
class Module extends Modules
{
    public $username, $role;
    public $user;

    public function __construct()
    {
        $this->username = $_SESSION['username'] ?? null;
        $this->role = $_SESSION['role'] ?? null;
        // ...
    }
}
```

**Key Methods:**
- `api_login()` - Sets user role during login
- `api_logout()` - Clears role from session
- Role assignment based on database `users.role` field

### Admin Module

**Location:** `modules/Admin/Module.php`

Provides administrative functionality with role checking:

```php
// Reads role from session (set during login)
$this->role = $_SESSION['role'] ?? null;
```

**Features:**
- Separate admin authentication via `api_login()` method
- Hardcoded development credentials (admin/mermaid, erik/mermaid)
- Role assignment during login process
- Admin-specific view templates

### Role Checking in Modules

```php
// Example role checking in module methods
public function admin_dashboard() 
{
    if ($_SESSION['role'] !== 'admin') {
        return $this->redirect('/login');
    }
    // Admin dashboard logic
}
```

### Role Checking in Firewall System

The firewall system provides built-in role checking:

```php
// Role firewall implementation
private static function checkRoleFirewall(array $config): FirewallResult
{
    $requiredRoles = $config['roles'] ?? [];
    $userRole = $_SESSION['role'] ?? null;

    if (empty($requiredRoles)) {
        return new FirewallResult(true);
    } elseif (!$userRole || !in_array($userRole, $requiredRoles)) {
        return new FirewallResult(false, 'Insufficient role permissions');
    } else {
        return new FirewallResult(true, null, ['user_role' => $userRole]);
    }
}
```

## Theme Integration

### Navbar Role-Based Display

**Location:** `web/themes/app/html/navbar.php`

```php
<?php if (!empty($_SESSION['role'])): ?>
    <li><a href="/admin">Admin Panel</a></li>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <li><a href="/admin/users">User Management</a></li>
<?php endif; ?>
```

### Conditional Content

```php
// In any view template
<?php if (in_array($_SESSION['role'] ?? '', ['admin', 'moderator'])): ?>
    <div class="admin-controls">
        <button>Delete</button>
        <button>Edit</button>
    </div>
<?php endif; ?>
```

## Multi-Tenancy and Roles

Each tenant can have domain-specific role configurations and custom role hierarchies.

### Domain-Specific Role Views

```
pages/
  _domains/
    client-a.com/
      admin/
        views/
          manager.html      # Custom role for client A
          supervisor.html
    client-b.com/
      admin/
        views/
          team_lead.html    # Different roles for client B
          coordinator.html
```

> **For Complete Multi-Tenancy Documentation:** See [MULTI_TENANCY.md](MULTI_TENANCY.md) for:
> - Tenant resolution strategies
> - Domain-based multi-tenancy setup
> - Tenant-specific configurations
> - Custom tenant resolvers

## Migration from Legacy Security

### Old Format (Still Supported)
```json
{
  "security": true,
  "public": false
}
```

### New Firewall Format
```json
{
  "firewalls": [
    {
      "type": "auth",
      "required": true
    }
  ]
}
```

The framework automatically converts legacy security settings to firewall rules for backward compatibility.

## Debugging and Logging

### Role Information in Session
```php
// Check current user role
var_dump($_SESSION['role']);
var_dump($_SESSION['username']);

// Available in all modules
echo $this->role;  // Current user role
echo $this->username;  // Current username
```

### Debug Role Resolution
```php
// In Controller.php, add debug output
error_log("Role-based view selected: " . $this->view_file);
error_log("User role: " . ($_SESSION['role'] ?? 'none'));
```

## API Endpoints and Roles

### Role-Protected API Methods

```php
// In module API methods
public function api_admin_function($controller, $method) 
{
    if ($_SESSION['role'] !== 'admin') {
        return ['error' => 'Admin access required'];
    }
    
    // Admin API logic here
}
```

### Role-Based API Responses
```php
public function api_dashboard($controller, $method)
{
    $role = $_SESSION['role'] ?? 'user';
    
    switch ($role) {
        case 'admin':
            return $this->getAdminDashboard();
        case 'moderator':
            return $this->getModeratorDashboard();
        default:
            return $this->getUserDashboard();
    }
}
```

## Common Patterns

### Progressive Enhancement
Start simple, add complexity as needed:

1. **Basic Authentication:** `{"type": "auth", "required": true}`
2. **Add Role Restriction:** `{"type": "role", "roles": ["admin"]}`
3. **Layer Additional Security:** See [FIREWALL.md](FIREWALL.md) for advanced patterns

### Graceful Degradation
Handle missing roles gracefully:
```php
$userRole = $_SESSION['role'] ?? 'guest';
$allowedFeatures = $this->getFeaturesForRole($userRole);
```

## Related Documentation

- **[FIREWALL.md](FIREWALL.md)** - Comprehensive firewall system documentation
- **[MULTI_TENANCY.md](MULTI_TENANCY.md)** - Multi-tenant setup and configuration
- **[DATABASE.md](DATABASE.md)** - Database schema and user management

This role system provides the foundation for building sophisticated access control while maintaining the simplicity that makes Yore framework easy to use. Start with basic roles and authentication, then enhance with the firewall system as your security requirements grow.
