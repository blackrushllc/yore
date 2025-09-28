<?php
/**
 * Example: Custom Tenant Resolution Configuration
 *
 * Place this file in your application bootstrap or include it in index.php
 * to customize how Yore resolves tenants for your specific use case.
 */

use App\TenantResolver;

// Enable debug logging during development
if (getenv('APP_ENV') === 'development') {
    TenantResolver::debug(true);
}

// Set a custom default tenant
TenantResolver::setDefault('local');

// Example 1: Database-driven tenant resolution
// This resolver checks a database table for tenant mappings
TenantResolver::addResolver(function() use ($database) {
    // Only run this if we have database connectivity
    if (!$database) return null;

    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!$host) return null;

    // Query your tenant mapping table
    $tenant = $database->query(
        "SELECT tenant_id FROM tenant_domains WHERE domain = ?",
        [$host]
    )->fetchColumn();

    return $tenant ?: null;
}, 250); // High priority

// Example 2: Multi-brand application
// Route different brands to different tenant configurations
TenantResolver::addResolver(function() {
    $brand = $_SERVER['HTTP_X_BRAND'] ?? $_GET['brand'] ?? null;

    $brandMapping = [
        'acme' => 'acme.example.com',
        'widgets' => 'widgets.example.com',
        'premium' => 'premium.example.com',
    ];

    return $brandMapping[$brand] ?? null;
}, 200);

// Example 3: User-based tenant override
// Allow logged-in users to switch between tenants they have access to
TenantResolver::addResolver(function() {
    if (!isset($_SESSION['user_id'])) return null;

    // Check if user has requested a specific tenant
    $requestedTenant = $_SESSION['tenant_override'] ?? $_GET['switch_tenant'] ?? null;

    if ($requestedTenant) {
        // Verify user has access to this tenant (implement your authorization logic)
        if (userHasAccessToTenant($_SESSION['user_id'], $requestedTenant)) {
            // Store the selection for future requests
            $_SESSION['tenant_override'] = $requestedTenant;
            return $requestedTenant;
        }
    }

    return null;
}, 180);

// Example 4: API key based tenant resolution
// Useful for API endpoints where tenant is determined by API key
TenantResolver::addResolver(function() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
    if (!$apiKey) return null;

    // Look up which tenant this API key belongs to
    $tenantMapping = [
        'ak_live_123abc' => 'client1.example.com',
        'ak_live_456def' => 'client2.example.com',
        'ak_test_789ghi' => 'staging.example.com',
    ];

    return $tenantMapping[$apiKey] ?? null;
}, 150);

// Example 5: Subdomain with fallback logic
// Custom subdomain mapping with more complex logic
TenantResolver::addResolver(function() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!$host) return null;

    $parts = explode('.', $host);
    if (count($parts) < 2) return null;

    $subdomain = $parts[0];

    // Handle special subdomains
    switch ($subdomain) {
        case 'www':
            return 'main.example.com'; // www.example.com -> main tenant
        case 'app':
            return 'app.yoreweb.com';  // app.example.com -> app tenant
        case 'admin':
            // Admin subdomain requires authentication
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                return 'admin.local';
            }
            return null; // Let other resolvers handle it
        case 'api':
            return 'api.local';
        default:
            // For other subdomains, check if tenant directory exists
            $tenantDir = __DIR__ . "/pages/_domains/{$subdomain}.example.com";
            return is_dir($tenantDir) ? "{$subdomain}.example.com" : null;
    }
}, 120);

// Example 6: Load balancer / proxy integration
// Handle X-Forwarded-Host and other proxy headers
TenantResolver::addResolver(function() {
    // Check if we're behind a proxy/load balancer
    $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
    $originalHost = $_SERVER['HTTP_X_ORIGINAL_HOST'] ?? null;

    $host = $forwardedHost ?: $originalHost;
    if (!$host) return null;

    // Map load balancer hosts to internal tenants
    $proxyMapping = [
        'lb1.internal.com' => 'production.example.com',
        'lb2.internal.com' => 'staging.example.com',
    ];

    return $proxyMapping[$host] ?? null;
}, 100);

/**
 * Helper function: Check if user has access to tenant
 */
function userHasAccessToTenant($userId, $tenantId): bool
{
    // Implement your authorization logic here
    // This could check a database, LDAP, or other auth system

    // Example implementation:
    global $database;
    if (!$database) return false;

    $access = $database->query(
        "SELECT 1 FROM user_tenant_access WHERE user_id = ? AND tenant_id = ?",
        [$userId, $tenantId]
    )->fetchColumn();

    return (bool) $access;
}

/**
 * Helper function: Get all tenants user has access to
 */
function getUserTenants($userId): array
{
    global $database;
    if (!$database) return [];

    return $database->query(
        "SELECT tenant_id, tenant_name FROM user_tenant_access 
         JOIN tenants ON user_tenant_access.tenant_id = tenants.id 
         WHERE user_id = ?",
        [$userId]
    )->fetchAll();
}

/**
 * Helper function: Create tenant switcher UI
 */
function renderTenantSwitcher(): string
{
    if (!isset($_SESSION['user_id'])) return '';

    $userTenants = getUserTenants($_SESSION['user_id']);
    $currentTenant = TenantResolver::current();

    if (count($userTenants) <= 1) return '';

    $html = '<div class="tenant-switcher dropdown">';
    $html .= '<button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">';
    $html .= 'Tenant: ' . htmlspecialchars($currentTenant);
    $html .= '</button>';
    $html .= '<div class="dropdown-menu">';

    foreach ($userTenants as $tenant) {
        $active = $tenant['tenant_id'] === $currentTenant ? 'active' : '';
        $html .= sprintf(
            '<a class="dropdown-item %s" href="?switch_tenant=%s">%s</a>',
            $active,
            urlencode($tenant['tenant_id']),
            htmlspecialchars($tenant['tenant_name'])
        );
    }

    $html .= '</div></div>';

    return $html;
}
