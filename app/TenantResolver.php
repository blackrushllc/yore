<?php
/*
 ▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
 ░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
 ░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
      ░                  ░
 Copyright (C) 2026, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

/app/TenantResolver.php - Flexible tenant/domain resolution with multiple fallback strategies

*/

namespace App;

/**
 * TenantResolver - Handles multi-tenant domain resolution with configurable strategies
 *
 * This class provides a flexible way to determine which tenant/domain should be used
 * for the current request, supporting multiple resolution strategies with fallbacks.
 */
class TenantResolver
{
    /**
     * @var array Custom resolver callbacks registered by the application
     */
    private static $customResolvers = [];

    /**
     * @var string Default tenant when all resolution strategies fail
     */
    private static $defaultTenant = 'local';

    /**
     * @var bool Enable debug logging for tenant resolution
     */
    private static $debug = false;

    /**
     * Resolve the current tenant using multiple strategies in order of priority
     *
     * Resolution order:
     * 1. Custom resolvers (user-defined)
     * 2. Environment variable (YORE_TENANT)
     * 3. Request header (X-Tenant)
     * 4. URL subdomain mapping
     * 5. HTTP Host header
     * 6. CLI detection
     * 7. Default fallback
     *
     * @return string The resolved tenant identifier
     */
    public static function resolve(): string
    {
        $strategies = [
            'custom' => [static::class, 'resolveCustom'],
            'environment' => [static::class, 'resolveFromEnvironment'],
            'header' => [static::class, 'resolveFromHeader'],
            'subdomain' => [static::class, 'resolveFromSubdomain'],
            'host' => [static::class, 'resolveFromHost'],
            'cli' => [static::class, 'resolveFromCli'],
            'default' => [static::class, 'resolveDefault']
        ];

        foreach ($strategies as $strategyName => $callback) {
            $tenant = call_user_func($callback);
            if ($tenant) {
                static::log("Resolved tenant '$tenant' using strategy: $strategyName");

                // Validate that the tenant directory exists
                if (static::validateTenant($tenant)) {
                    return $tenant;
                } else {
                    static::log("Tenant '$tenant' invalid (directory not found), continuing...");
                }
            }
        }

        // Final fallback
        static::log("All strategies failed, using default: " . static::$defaultTenant);
        return static::$defaultTenant;
    }

    /**
     * Register a custom tenant resolver
     *
     * @param callable $resolver Function that returns tenant string or null
     * @param int $priority Higher numbers run first (default: 100)
     */
    public static function addResolver(callable $resolver, int $priority = 100): void
    {
        static::$customResolvers[$priority][] = $resolver;
        krsort(static::$customResolvers); // Sort by priority desc
    }

    /**
     * Set the default tenant fallback
     *
     * @param string $tenant Default tenant identifier
     */
    public static function setDefault(string $tenant): void
    {
        static::$defaultTenant = $tenant;
    }

    /**
     * Enable or disable debug logging
     *
     * @param bool $enabled
     */
    public static function debug(bool $enabled = true): void
    {
        static::$debug = $enabled;
    }

    /**
     * Try custom resolvers first (highest priority)
     */
    private static function resolveCustom(): ?string
    {
        foreach (static::$customResolvers as $priority => $resolvers) {
            foreach ($resolvers as $resolver) {
                $result = call_user_func($resolver);
                if ($result && is_string($result)) {
                    return $result;
                }
            }
        }
        return null;
    }

    /**
     * Resolve from environment variable
     */
    private static function resolveFromEnvironment(): ?string
    {
        return $_ENV['YORE_TENANT'] ?? getenv('YORE_TENANT') ?: null;
    }

    /**
     * Resolve from HTTP header (useful for API calls, load balancers)
     */
    private static function resolveFromHeader(): ?string
    {
        return $_SERVER['HTTP_X_TENANT'] ?? null;
    }

    /**
     * Resolve from subdomain mapping
     * Maps subdomains to tenant identifiers
     */
    private static function resolveFromSubdomain(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        if (!$host) return null;

        // Extract subdomain
        $parts = explode('.', $host);
        if (count($parts) < 3) return null; // No subdomain

        $subdomain = $parts[0];

        // Define your subdomain -> tenant mapping here
        // This could be loaded from config file or database
        $subdomainMap = [
            'app' => 'app.yoreweb.com',
            'admin' => 'admin.local',
            'api' => 'api.local',
            // Add more mappings as needed
        ];

        return $subdomainMap[$subdomain] ?? null;
    }

    /**
     * Resolve from HTTP host header (current behavior)
     */
    private static function resolveFromHost(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        // Clean up host (remove port if present)
        if ($host && strpos($host, ':') !== false) {
            $host = explode(':', $host)[0];
        }

        return $host ?: null;
    }

    /**
     * Resolve for CLI usage
     */
    private static function resolveFromCli(): ?string
    {
        if (php_sapi_name() === 'cli') {
            // Check for CLI argument --tenant=example.com
            global $argv;
            if ($argv) {
                foreach ($argv as $arg) {
                    if (strpos($arg, '--tenant=') === 0) {
                        return substr($arg, 9);
                    }
                }
            }
            // CLI defaults to 'local'
            return 'local';
        }
        return null;
    }

    /**
     * Default fallback resolver
     */
    private static function resolveDefault(): string
    {
        return static::$defaultTenant;
    }

    /**
     * Validate that a tenant exists (has a directory structure)
     */
    private static function validateTenant(string $tenant): bool
    {
        $path = __DIR__ . "/../pages/_domains/$tenant";
        return is_dir($path);
    }

    /**
     * Log debug information if debugging is enabled
     */
    private static function log(string $message): void
    {
        if (static::$debug) {
            error_log("[TenantResolver] $message");
        }
    }

    /**
     * Get current tenant with caching for performance
     */
    private static $cachedTenant = null;

    public static function current(): string
    {
        if (static::$cachedTenant === null) {
            static::$cachedTenant = static::resolve();
        }
        return static::$cachedTenant;
    }

    /**
     * Clear the cached tenant (useful for testing)
     */
    public static function clearCache(): void
    {
        static::$cachedTenant = null;
    }

    /**
     * Get all available tenants by scanning the _domains directory
     */
    public static function getAllTenants(): array
    {
        $domainsPath = __DIR__ . '/../pages/_domains';
        if (!is_dir($domainsPath)) {
            return ['local'];
        }

        $tenants = [];
        $directories = scandir($domainsPath);

        foreach ($directories as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir("$domainsPath/$dir")) {
                $tenants[] = $dir;
            }
        }

        return $tenants;
    }
}
