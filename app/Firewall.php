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

MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

######## #### ########  ######## ##      ##    ###    ##       ##
##        ##  ##     ## ##       ##  ##  ##   ## ##   ##       ##
##        ##  ##     ## ##       ##  ##  ##  ##   ##  ##       ##
######    ##  ########  ######   ##  ##  ## ##     ## ##       ##
##        ##  ##   ##   ##       ##  ##  ## ######### ##       ##
##        ##  ##    ##  ##       ##  ##  ## ##     ## ##       ##
##       #### ##     ## ########  ###  ###  ##     ## ######## ########

namespace App;

/**
 * Firewall system for controlling page access with multiple configurable rules
 *
 * Supports multi-tenancy and can be configured via page JSON or hierarchical firewalls.json files
 * Built-in firewalls: IP, Authentication, Role, Entity ownership
 * Custom firewalls can be registered per-tenant
 */
class Firewall
{
    private static $registeredFirewalls = [];
    private static $globalFirewalls = [];
    private static $configCache = [];
    private static $cacheEnabled = true;
    private static $cacheDir = null;
    private static $debugMode = false;
    private static $developmentMode = null; // Auto-detect or manually set
    private static $debugResults = []; // Store debug info about firewall execution
    private static $currentPageData = []; // Store current page data for debug context

    /**
     * Enable/disable caching with development mode detection
     * @param bool $enabled Enable or disable caching
     * @param string $cacheDir Optional cache directory (defaults to ../storage/cache/firewalls)
     * @param bool $forceDevelopmentMode Force development mode (auto-detects if null)
     */
    public static function setCaching(bool $enabled, string $cacheDir = null, bool $forceDevelopmentMode = null): void
    {
        self::$cacheEnabled = $enabled;
        self::$cacheDir = $cacheDir ?: '../storage/cache/firewalls';

        // Auto-detect or force development mode
        if ($forceDevelopmentMode !== null) {
            self::$developmentMode = $forceDevelopmentMode;
        } elseif (self::$developmentMode === null) {
            // Use framework's development mode detection if available, otherwise fallback to basic checks
            self::$developmentMode = self::isFrameworkDevelopmentMode();
        }

        // In development mode, disable caching by default unless explicitly enabled
        if (self::$developmentMode && $enabled === true && $forceDevelopmentMode === null) {
            // User explicitly wants caching in dev mode
            self::$cacheEnabled = true;
            error_log("Firewall: Caching explicitly enabled in development mode");
        } elseif (self::$developmentMode) {
            // Default behavior: no caching in development
            self::$cacheEnabled = false;
            error_log("Firewall: Development mode detected - caching disabled for live config changes");
        }

        // Create cache directory if it doesn't exist
        if (self::$cacheEnabled && !is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    /**
     * Check if framework is in development mode
     * Uses basic environment indicators
     */
    private static function isFrameworkDevelopmentMode(): bool
    {
        // Check for common debug constants
        if (defined('DEBUG') && DEBUG) return true;
        if (defined('APP_DEBUG') && APP_DEBUG) return true;

        // Check environment variables
        $env = $_ENV['APP_ENV'] ?? $_ENV['NODE_ENV'] ?? $_SERVER['APP_ENV'] ?? null;
        if (in_array($env, ['development', 'dev', 'local'])) return true;

        // Check for localhost/local domains
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) return true;
        if (strpos($host, '.local') !== false) return true;

        // Check for development file markers
        if (file_exists('../.env.development') || file_exists('../.env.local')) return true;
        if (file_exists('../composer.json')) return true; // Development environment indicator

        return false;
    }

    /**
     * Enable debug mode for firewall operations
     */
    public static function setDebugMode(bool $enabled): void
    {
        self::$debugMode = $enabled;
    }

    /**
     * Check if debug mode is enabled (from global setting or page config)
     * Only allow debug for authenticated admin users in production
     * Allow debug for all users in development mode
     */
    public static function isDebugEnabled(array $pageData = null): bool
    {
        // Use stored page data if none provided
        if ($pageData === null) {
            $pageData = self::$currentPageData;
        }

        // In development mode, allow debug for all users
        if (self::$developmentMode) {
            return self::$debugMode || !empty($pageData['debug_firewall']);
        }

        // In production mode, only allow debug for authenticated admin users
        if (!self::isAdminUser()) {
            return false;
        }

        return self::$debugMode || !empty($pageData['debug_firewall']);
    }

    /**
     * Warm up the firewall cache for a specific page or entire domain
     *
     * @param string $domain Domain to warm cache for
     * @param string $site Optional specific site (null for all sites)
     * @return array Statistics about cache warming
     */
    public static function warmCache(string $domain = 'default', string $site = null): array
    {
        $stats = [
            'files_processed' => 0,
            'files_cached' => 0,
            'errors' => []
        ];

        // Build paths to check based on domain and site
        $basePath = $domain === 'default' ? '../pages' : "../pages/_domains/{$domain}";

        if (!is_dir($basePath)) {
            $stats['errors'][] = "Domain directory not found: {$basePath}";
            return $stats;
        }

        // Find all firewalls.json files
        $firewallFiles = self::findFirewallFiles($basePath, $site);

        foreach ($firewallFiles as $file) {
            $stats['files_processed']++;

            try {
                $config = self::loadFirewallConfig($file);
                if ($config !== null) {
                    $stats['files_cached']++;
                }
            } catch (\Exception $e) {
                $stats['errors'][] = "Error caching {$file}: " . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Find all firewalls.json files in a directory tree
     *
     * @param string $basePath Base path to search
     * @param string $site Optional site filter
     * @return array Array of firewall file paths
     */
    private static function findFirewallFiles(string $basePath, string $site = null): array
    {
        $files = [];

        if ($site) {
            // Search specific site only
            $siteDir = "{$basePath}/{$site}";
            if (is_dir($siteDir)) {
                $files = array_merge($files, glob($siteDir . '/firewalls.json'));
                $files = array_merge($files, glob($siteDir . '/**/firewalls.json', GLOB_BRACE));
            }
        } else {
            // Search all directories
            $files = array_merge($files, glob($basePath . '/firewalls.json'));
            $files = array_merge($files, glob($basePath . '/**/firewalls.json', GLOB_BRACE));
        }

        return array_filter($files, 'is_file');
    }

    /**
     * Register a custom firewall class
     *
     * @param string $name Firewall identifier
     * @param string $className Full class name implementing FirewallInterface
     * @param string $tenant Optional tenant-specific registration
     */
    public static function register(string $name, string $className, string $tenant = null): void
    {
        if ($tenant) {
            self::$registeredFirewalls[$tenant][$name] = $className;
        } else {
            self::$globalFirewalls[$name] = $className;
        }
    }

    /**
     * Check if access is allowed based on firewall rules
     *
     * @param array $firewalls Array of firewall configurations from page JSON
     * @param array $pageData Complete page data for context
     * @param string $tenant Current tenant identifier
     * @return FirewallResult
     */
    public static function check(array $firewalls, array $pageData = [], string $tenant = null): FirewallResult
    {
        // Clear previous debug results for this check
        self::$debugResults = [];
        
        // Store current page data for debug context
        self::$currentPageData = $pageData;

        // Get hierarchical firewalls from directory structure
        $hierarchicalFirewalls = self::getHierarchicalFirewalls($pageData, $tenant);

        // Combine hierarchical firewalls with page-specific firewalls
        $allFirewalls = array_merge($hierarchicalFirewalls, $firewalls);

        $result = new FirewallResult(true);

        foreach ($allFirewalls as $firewallConfig) {
            $firewallResult = self::executeFirewall($firewallConfig, $pageData, $tenant);

            if (!$firewallResult->allowed) {
                // If debug is enabled, output debug info before returning failure
                if (self::isDebugEnabled($pageData)) {
                    echo self::renderDebugInfo($pageData, $tenant);
                }
                return $firewallResult; // First failure blocks access
            }

            // Merge any additional data from successful checks
            $result->mergeData($firewallResult->data);
        }

        // If debug is enabled and we've completed all checks successfully, output debug info
        if (self::isDebugEnabled($pageData)) {
            echo self::renderDebugInfo($pageData, $tenant);
        }

        return $result;
    }

    /**
     * Get firewall rules from hierarchical firewalls.json files
     *
     * @param array $pageData Page data containing domain, site, and page info
     * @param string $tenant Current tenant
     * @return array Array of firewall configurations
     */
    private static function getHierarchicalFirewalls(array $pageData, string $tenant = null): array
    {
        $firewalls = [];
        $domain = $pageData['domain'] ?? 'default';
        $site = $pageData['site'] ?? 'default';
        $page = $pageData['page'] ?? 'home';

        // Build path hierarchy from most general to most specific
        $paths = [];

        // 1. Global default (if no domain-specific config)
        $paths[] = "../pages/firewalls.json";

        // 2. Domain level
        $paths[] = "../pages/_domains/{$domain}/firewalls.json";

        // 3. Site level
        if ($site !== 'default') {
            $paths[] = "../pages/_domains/{$domain}/{$site}/firewalls.json";
        } else {
            $paths[] = "../pages/{$site}/firewalls.json";
        }

        // 4. Page directory level (for nested pages)
        $pageParts = explode('/', trim($page, '/'));
        if (count($pageParts) > 1) {
            $currentPath = $site !== 'default'
                ? "../pages/_domains/{$domain}/{$site}"
                : "../pages/{$site}";

            for ($i = 0; $i < count($pageParts) - 1; $i++) {
                $currentPath .= '/' . $pageParts[$i];
                $paths[] = "{$currentPath}/firewalls.json";
            }
        }

        // Load and merge firewall configurations
        foreach ($paths as $path) {
            $config = self::loadFirewallConfig($path);
            if ($config) {
                $firewalls = array_merge($firewalls, $config);
            }
        }

        return $firewalls;
    }

    /**
     * Load firewall configuration from a firewalls.json file with caching
     *
     * @param string $path Path to firewalls.json file
     * @return array|null Firewall configurations or null if file doesn't exist
     */
    private static function loadFirewallConfig(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        // Check memory cache first
        if (isset(self::$configCache[$path])) {
            return self::$configCache[$path];
        }

        // Check file cache if enabled
        if (self::$cacheEnabled) {
            $cached = self::loadFromFileCache($path);
            if ($cached !== null) {
                self::$configCache[$path] = $cached;
                return $cached;
            }
        }

        // Load and parse the configuration file
        $content = file_get_contents($path);
        if (!$content) {
            return null;
        }

        $config = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = "Invalid JSON in firewall config: {$path} - " . json_last_error_msg();
            error_log($error);

            // In debug mode, throw exception instead of silently failing
            if (defined('DEBUG') && DEBUG) {
                throw new \RuntimeException($error);
            }

            return null;
        }

        // Process the configuration
        $firewalls = [];
        if (isset($config['inherit']) && $config['inherit'] === false) {
            $firewalls = $config['firewalls'] ?? [];
        } else {
            $firewalls = $config['firewalls'] ?? [];
        }

        // Cache the result
        self::$configCache[$path] = $firewalls;

        if (self::$cacheEnabled) {
            self::saveToFileCache($path, $firewalls);
        }

        return $firewalls;
    }

    /**
     * Load configuration from file cache with security validation
     *
     * @param string $path Original config file path
     * @return array|null Cached configuration or null if cache miss/invalid
     */
    private static function loadFromFileCache(string $path): ?array
    {
        $cacheKey = self::getCacheKey($path);
        $cacheFile = self::$cacheDir . '/' . $cacheKey . '.php';

        if (!file_exists($cacheFile)) {
            return null;
        }

        try {
            // Define security constant before including cache file
            if (!defined('FIREWALL_CACHE_ACCESS')) {
                define('FIREWALL_CACHE_ACCESS', true);
            }
            $cached = include $cacheFile;
            return is_array($cached) ? $cached : null;
        } catch (\Exception $e) {
            // Cache file corrupted, delete it
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            error_log("Firewall: Corrupted cache file deleted: {$cacheFile}");
            return null;
        }
    }

    /**
     * Save configuration to file cache with security validation
     *
     * @param string $path Original config file path
     * @param array $config Configuration data
     */
    private static function saveToFileCache(string $path, array $config): void
    {
        if (!self::$cacheEnabled) {
            return;
        }

        // Validate path to prevent directory traversal
        $realPath = realpath($path);
        if (!$realPath || !self::isValidConfigPath($realPath)) {
            error_log("Firewall: Invalid cache path attempted: {$path}");
            return;
        }

        $cacheKey = self::getCacheKey($path);
        $cacheFile = self::$cacheDir . '/' . $cacheKey . '.php';

        // Generate PHP cache file content with security headers
        $content = "<?php\n";
        $content .= "// Firewall cache for: " . addslashes($path) . "\n";
        $content .= "// Generated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "// Security: This file is auto-generated, do not edit manually\n";
        $content .= "if (!defined('FIREWALL_CACHE_ACCESS')) {\n";
        $content .= "    http_response_code(403);\n";
        $content .= "    exit('Access denied');\n";
        $content .= "}\n";
        $content .= "return " . var_export($config, true) . ";\n";

        // Use atomic write with proper error handling
        $tempFile = $cacheFile . '.tmp.' . uniqid();
        if (file_put_contents($tempFile, $content, LOCK_EX) !== false) {
            if (rename($tempFile, $cacheFile)) {
                chmod($cacheFile, 0644); // Restrict permissions
            } else {
                // Clean up temp file if rename failed
                unlink($tempFile);
            }
        }
    }

    /**
     * Validate that config path is within allowed directories
     */
    private static function isValidConfigPath(string $path): bool
    {
        $allowedPaths = [
            realpath('../pages'),
            realpath('../pages/_domains')
        ];

        foreach ($allowedPaths as $allowedPath) {
            if ($allowedPath && strpos($path, $allowedPath) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate cache key from file path
     *
     * @param string $path File path
     * @return string Cache key
     */
    private static function getCacheKey(string $path): string
    {
        return md5($path);
    }

    /**
     * Clear firewall configuration cache
     *
     * @param string $path Optional specific path to clear, or null to clear all
     */
    public static function clearCache(string $path = null): void
    {
        if ($path) {
            // Clear specific cache entry
            unset(self::$configCache[$path]);

            if (self::$cacheEnabled) {
                $cacheKey = self::getCacheKey($path);
                $cacheFile = self::$cacheDir . '/' . $cacheKey . '.php';
                if (file_exists($cacheFile)) {
                    unlink($cacheFile);
                }
            }
        } else {
            // Clear all cache
            self::$configCache = [];

            if (self::$cacheEnabled && is_dir(self::$cacheDir)) {
                $files = glob(self::$cacheDir . '/*.php');
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Get cache statistics
     *
     * @return array Cache statistics
     */
    public static function getCacheStats(): array
    {
        $stats = [
            'enabled' => self::$cacheEnabled,
            'cache_dir' => self::$cacheDir,
            'memory_cache_entries' => count(self::$configCache),
            'file_cache_entries' => 0,
            'cache_size_bytes' => 0
        ];

        if (self::$cacheEnabled && is_dir(self::$cacheDir)) {
            $files = glob(self::$cacheDir . '/*.php');
            $stats['file_cache_entries'] = count($files);

            foreach ($files as $file) {
                $stats['cache_size_bytes'] += filesize($file);
            }
        }

        return $stats;
    }

    /**
     * Execute a single firewall rule
     */
    private static function executeFirewall(array $config, array $pageData, string $tenant = null): FirewallResult
    {
        $type = $config['type'] ?? null;

        if (!$type) {
            return new FirewallResult(false, 'Firewall type not specified');
        }

        // Look for custom firewall first (tenant-specific, then global)
        $firewallClass = null;
        if ($tenant && isset(self::$registeredFirewalls[$tenant][$type])) {
            $firewallClass = self::$registeredFirewalls[$tenant][$type];
        } elseif (isset(self::$globalFirewalls[$type])) {
            $firewallClass = self::$globalFirewalls[$type];
        }

        // If custom firewall found, use it
        if ($firewallClass && class_exists($firewallClass)) {
            $firewall = new $firewallClass();
            if ($firewall instanceof FirewallInterface) {
                $result = $firewall->check($config, $pageData);

                // Store debug result
                if (self::$debugMode) {
                    self::$debugResults[] = [
                        'firewall' => $type,
                        'allowed' => $result->allowed,
                        'message' => $result->message,
                        'data' => $result->data
                    ];
                }

                return $result;
            }
        }

        // Use built-in firewalls
        switch ($type) {
            case 'ip':
                return self::checkIpFirewall($config);
            case 'auth':
                return self::checkAuthFirewall($config);
            case 'role':
                return self::checkRoleFirewall($config);
            case 'entity':
                return self::checkEntityFirewall($config, $pageData);
            case 'public':
                return new FirewallResult(true); // Always allow
            default:
                return new FirewallResult(false, "Unknown firewall type: {$type}");
        }
    }

    /**
     * IP-based firewall
     */
    private static function checkIpFirewall(array $config): FirewallResult
    {
        $clientIp = self::getClientIp();
        $mode = $config['mode'] ?? 'whitelist'; // whitelist or blacklist
        $ips = $config['ips'] ?? [];

        $isInList = false;
        foreach ($ips as $ip) {
            if (self::ipMatches($clientIp, $ip)) {
                $isInList = true;
                break;
            }
        }

        $allowed = ($mode === 'whitelist') ? $isInList : !$isInList;
        $message = $allowed ? null : "IP {$clientIp} not allowed";
        $data = ['client_ip' => $clientIp, 'mode' => $mode, 'allowed_ips' => $ips];

        $result = new FirewallResult($allowed, $message, $data);

        // Store debug result
        if (self::isDebugEnabled()) {
            self::$debugResults[] = [
                'firewall' => 'ip',
                'config' => $config,
                'allowed' => $result->allowed,
                'message' => $result->message,
                'data' => $result->data
            ];
        }

        return $result;
    }

    /**
     * Authentication firewall with secure session validation
     */
    private static function checkAuthFirewall(array $config): FirewallResult
    {
        $required = $config['required'] ?? true;
        
        // Validate session integrity and check if user is properly authenticated
        $isLoggedIn = self::isValidAuthenticatedSession();

        if ($required && !$isLoggedIn) {
            $result = new FirewallResult(false, 'Authentication required');
        } elseif (!$required && $isLoggedIn && isset($config['redirect_if_logged_in'])) {
            $result = new FirewallResult(false, 'Already logged in', ['redirect' => $config['redirect_if_logged_in']]);
        } else {
            $result = new FirewallResult(true, null, [
                'user' => $_SESSION['username'] ?? null,
                'user_id' => $_SESSION['user_id'] ?? null,
                'logged_in' => $isLoggedIn
            ]);
        }

        // Store debug result
        if (self::isDebugEnabled()) {
            self::$debugResults[] = [
                'firewall' => 'auth',
                'config' => $config,
                'allowed' => $result->allowed,
                'message' => $result->message,
                'data' => $result->data
            ];
        }

        return $result;
    }

    /**
     * Validate session integrity and authentication status
     */
    private static function isValidAuthenticatedSession(): bool
    {
        // Check if session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        // Check required session variables
        if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
            return false;
        }

        // Validate user_id is numeric
        if (!is_numeric($_SESSION['user_id'])) {
            return false;
        }

        // Check session token if present (CSRF protection)
        if (isset($_SESSION['session_token'])) {
            $expectedToken = self::generateSessionToken($_SESSION['user_id'], $_SESSION['username']);
            if (!hash_equals($_SESSION['session_token'], $expectedToken)) {
                // Invalid session token - possible session hijacking
                session_destroy();
                return false;
            }
        }

        // Check session expiration
        if (isset($_SESSION['last_activity'])) {
            $sessionTimeout = 1800; // 30 minutes
            if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
                session_destroy();
                return false;
            }
            // Update last activity
            $_SESSION['last_activity'] = time();
        }

        return true;
    }

    /**
     * Generate secure session token for CSRF protection
     */
    private static function generateSessionToken(string $userId, string $username): string
    {
        $secret = $_ENV['SESSION_SECRET'] ?? 'default_secret_change_in_production';
        return hash_hmac('sha256', $userId . $username . session_id(), $secret);
    }

    /**
     * Role-based firewall
     */
    private static function checkRoleFirewall(array $config): FirewallResult
    {
        $requiredRoles = $config['roles'] ?? [];
        $userRole = $_SESSION['role'] ?? null;

        if (empty($requiredRoles)) {
            $result = new FirewallResult(true);
        } elseif (!$userRole || !in_array($userRole, $requiredRoles)) {
            $result = new FirewallResult(false, 'Insufficient role permissions');
        } else {
            $result = new FirewallResult(true, null, ['user_role' => $userRole]);
        }

        // Store debug result
        if (self::isDebugEnabled()) {
            self::$debugResults[] = [
                'firewall' => 'role',
                'config' => $config,
                'allowed' => $result->allowed,
                'message' => $result->message,
                'data' => array_merge($result->data, [
                    'required_roles' => $requiredRoles,
                    'current_role' => $userRole
                ])
            ];
        }

        return $result;
    }

    /**
     * Entity ownership firewall with secure validation
     */
    private static function checkEntityFirewall(array $config, array $pageData): FirewallResult
    {
        $entityField = $config['field'] ?? 'created_by_user_id';
        
        // Require valid authenticated session for security
        if (!self::isValidAuthenticatedSession()) {
            return new FirewallResult(false, 'User not authenticated for entity check');
        }
        
        $userId = $_SESSION['user_id'] ?? null;

        // Get entity value from page data (this is the primary and intended use case)
        $entityValue = $pageData[$entityField] ?? null;

        if ($entityValue === null || !is_numeric($entityValue)) {
            return new FirewallResult(false, "Entity ownership cannot be verified");
        }

        // Use strict comparison for security
        $allowed = ((int)$entityValue === (int)$userId);
        $result = new FirewallResult(
            $allowed,
            $allowed ? null : 'Entity access denied',
            ['entity_owner' => $entityValue, 'user_id' => $userId]
        );

        // Store debug result
        if (self::isDebugEnabled()) {
            self::$debugResults[] = [
                'firewall' => 'entity',
                'config' => $config,
                'allowed' => $result->allowed,
                'message' => $result->message,
                'data' => $result->data
            ];
        }

        return $result;
    }


    /**
     * Get client IP address considering proxies with proper validation
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Shared internet
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                // Validate IP address properly
                if (self::isValidIpAddress($ip)) {
                    return $ip;
                }
            }
        }

        $fallbackIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return self::isValidIpAddress($fallbackIp) ? $fallbackIp : '0.0.0.0';
    }

    /**
     * Validate IP address (IPv4 and IPv6)
     */
    private static function isValidIpAddress(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if IP matches pattern (supports CIDR) with proper validation
     */
    private static function ipMatches(string $ip, string $pattern): bool
    {
        // Validate inputs
        if (!self::isValidIpAddress($ip)) {
            return false;
        }

        if ($ip === $pattern) {
            return true;
        }

        // CIDR notation support with bounds checking
        if (strpos($pattern, '/') !== false) {
            $parts = explode('/', $pattern);
            if (count($parts) !== 2) {
                return false;
            }

            list($subnet, $bits) = $parts;
            
            // Validate subnet IP
            if (!self::isValidIpAddress($subnet)) {
                return false;
            }

            // Validate CIDR bits (0-32 for IPv4, 0-128 for IPv6)
            $bits = (int)$bits;
            $maxBits = (strpos($ip, ':') !== false) ? 128 : 32;
            if ($bits < 0 || $bits > $maxBits) {
                return false;
            }

            return self::cidrMatch($ip, $subnet, $bits);
        }

        return false;
    }

    /**
     * Check if IP matches CIDR subnet using safe bitwise operations
     */
    private static function cidrMatch(string $ip, string $subnet, int $bits): bool
    {
        // Use inet_pton for proper IPv4/IPv6 support
        $ipBinary = inet_pton($ip);
        $subnetBinary = inet_pton($subnet);
        
        if ($ipBinary === false || $subnetBinary === false) {
            return false;
        }

        // Calculate number of bytes to check
        $bytesToCheck = (int)ceil($bits / 8);
        $bitsInLastByte = $bits % 8;
        
        // Check full bytes
        for ($i = 0; $i < $bytesToCheck - 1; $i++) {
            if ($ipBinary[$i] !== $subnetBinary[$i]) {
                return false;
            }
        }
        
        // Check partial byte if needed
        if ($bitsInLastByte > 0) {
            $mask = 0xFF << (8 - $bitsInLastByte);
            if ((ord($ipBinary[$bytesToCheck - 1]) & $mask) !== (ord($subnetBinary[$bytesToCheck - 1]) & $mask)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if current user is an admin (for debug access)
     */
    private static function isAdminUser(): bool
    {
        // Must be authenticated first
        if (!self::isValidAuthenticatedSession()) {
            return false;
        }

        $userRole = $_SESSION['role'] ?? null;
        $adminRoles = ['admin', 'super_admin', 'administrator'];
        
        return in_array($userRole, $adminRoles, true); // Use strict comparison
    }

    /**
     * Get debugging information about applied firewalls
     *
     * @param array $pageData Page data for debugging
     * @param string $tenant Current tenant
     * @return array Debug information
     */
    public static function getDebugInfo(array $pageData, string $tenant = null): array
    {
        $domain = $pageData['domain'] ?? 'default';
        $site = $pageData['site'] ?? 'default';
        $page = $pageData['page'] ?? 'home';

        // Build the same path hierarchy as getHierarchicalFirewalls
        $paths = [];
        $paths[] = "../pages/firewalls.json";
        $paths[] = "../pages/_domains/{$domain}/firewalls.json";

        if ($site !== 'default') {
            $paths[] = "../pages/_domains/{$domain}/{$site}/firewalls.json";
        } else {
            $paths[] = "../pages/{$site}/firewalls.json";
        }

        // Page directory level for nested pages
        $pageParts = explode('/', trim($page, '/'));
        if (count($pageParts) > 1) {
            $currentPath = $site !== 'default'
                ? "../pages/_domains/{$domain}/{$site}"
                : "../pages/{$site}";

            for ($i = 0; $i < count($pageParts) - 1; $i++) {
                $currentPath .= '/' . $pageParts[$i];
                $paths[] = "{$currentPath}/firewalls.json";
            }
        }

        $debugInfo = [
            'development_mode' => self::$developmentMode,
            'cache_enabled' => self::$cacheEnabled,
            'debug_enabled' => self::isDebugEnabled($pageData),
            'tenant' => $tenant,
            'page_info' => [
                'domain' => $domain,
                'site' => $site,
                'page' => $page
            ],
            'firewall_paths' => [],
            'applied_firewalls' => [],
            'page_firewalls' => $pageData['firewalls'] ?? [],
            'execution_results' => self::$debugResults,
            'cache_stats' => self::getCacheStats()
        ];

        // Check each path for firewall config
        foreach ($paths as $path) {
            $pathInfo = [
                'path' => $path,
                'exists' => file_exists($path),
                'cached' => isset(self::$configCache[$path]),
                'firewalls' => []
            ];

            if ($pathInfo['exists']) {
                $config = self::loadFirewallConfig($path);
                $pathInfo['firewalls'] = $config ?? [];
                $pathInfo['inherit'] = true; // Default inheritance

                // Check for inheritance setting in the actual file
                if (file_exists($path)) {
                    $content = file_get_contents($path);
                    $fullConfig = json_decode($content, true);
                    if (is_array($fullConfig) && array_key_exists('inherit', $fullConfig)) {
                        $pathInfo['inherit'] = $fullConfig['inherit'];
                    }
                }
            }

            $debugInfo['firewall_paths'][] = $pathInfo;
        }

        // Get hierarchical firewalls that would be applied
        $debugInfo['applied_firewalls'] = self::getHierarchicalFirewalls($pageData, $tenant);

        return $debugInfo;
    }

    /**
     * Render debug information as HTML with security filtering
     *
     * @param array $pageData Page data for debugging
     * @param string $tenant Current tenant
     * @return string HTML debug output
     */
    public static function renderDebugInfo(array $pageData, string $tenant = null): string
    {
        if (!self::isDebugEnabled($pageData)) {
            return '';
        }

        $debugInfo = self::getDebugInfo($pageData, $tenant);
        
        // Sanitize sensitive data before rendering
        $debugInfo = self::sanitizeDebugInfo($debugInfo);

        $html = '<div style="background: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin: 10px 0; font-family: monospace; font-size: 12px; line-height: 1.4;">';
        $html .= '<h3 style="margin: 0 0 10px 0; color: #333;">🔒 Firewall Debug Information</h3>';

        // Environment info
        $html .= '<div style="margin-bottom: 10px;">';
        $html .= '<strong>Environment:</strong> ';
        $html .= $debugInfo['development_mode'] ? '🔧 Development' : '🚀 Production';
        $html .= ' | <strong>Cache:</strong> ' . ($debugInfo['cache_enabled'] ? '✅ Enabled' : '❌ Disabled');
        $html .= ' | <strong>Tenant:</strong> ' . ($debugInfo['tenant'] ?: 'default');
        $html .= '</div>';

        // Page info
        $html .= '<div style="margin-bottom: 10px;">';
        $html .= '<strong>Page:</strong> ' . $debugInfo['page_info']['domain'] . ' / ';
        $html .= $debugInfo['page_info']['site'] . ' / ' . $debugInfo['page_info']['page'];
        $html .= '</div>';

        // Firewall paths
        $html .= '<div style="margin-bottom: 10px;">';
        $html .= '<strong>Configuration Files (hierarchy order):</strong><br>';
        foreach ($debugInfo['firewall_paths'] as $pathInfo) {
            $icon = $pathInfo['exists'] ? '📄' : '❌';
            $cached = $pathInfo['cached'] ? ' (cached)' : '';
            $inherit = $pathInfo['exists'] && !$pathInfo['inherit'] ? ' [inherit: false]' : '';
            $html .= "{$icon} {$pathInfo['path']}{$cached}{$inherit}<br>";

            if ($pathInfo['exists'] && !empty($pathInfo['firewalls'])) {
                foreach ($pathInfo['firewalls'] as $fw) {
                    $html .= "&nbsp;&nbsp;&nbsp;&nbsp;└─ {$fw['type']}<br>";
                }
            }
        }
        $html .= '</div>';

        // Applied firewalls
        if (!empty($debugInfo['applied_firewalls'])) {
            $html .= '<div style="margin-bottom: 10px;">';
            $html .= '<strong>Applied Firewalls (from hierarchy):</strong><br>';
            foreach ($debugInfo['applied_firewalls'] as $fw) {
                $html .= "🛡️ {$fw['type']}<br>";
            }
            $html .= '</div>';
        }

        // Page-specific firewalls
        if (!empty($debugInfo['page_firewalls'])) {
            $html .= '<div style="margin-bottom: 10px;">';
            $html .= '<strong>Page-Specific Firewalls:</strong><br>';
            foreach ($debugInfo['page_firewalls'] as $fw) {
                $html .= "🔐 {$fw['type']}<br>";
            }
            $html .= '</div>';
        }

        // Execution Results
        if (!empty($debugInfo['execution_results'])) {
            $html .= '<div style="margin-bottom: 10px; padding-top: 10px; border-top: 1px solid #ddd;">';
            $html .= '<strong>Execution Results:</strong><br>';
            foreach ($debugInfo['execution_results'] as $result) {
                $status = $result['allowed'] ? '✅ Allowed' : '❌ Denied';
                $message = $result['message'] ?: 'No message';
                $html .= "{$status} {$result['firewall']} - {$message}<br>";

                // Show additional debug data if available
                if (!empty($result['data']) && is_array($result['data'])) {
                    foreach ($result['data'] as $key => $value) {
                        if (is_scalar($value)) {
                            $html .= "&nbsp;&nbsp;&nbsp;&nbsp;{$key}: " . htmlspecialchars($value) . "<br>";
                        }
                    }
                }
            }
            $html .= '</div>';
        }

        // Cache stats
        $stats = $debugInfo['cache_stats'];
        $html .= '<div style="margin-bottom: 10px; font-size: 11px; color: #666;">';
        $html .= '<strong>Cache Stats:</strong> ';
        $html .= "Memory: {$stats['memory_cache_entries']} entries, ";
        $html .= "File: {$stats['file_cache_entries']} entries, ";
        $html .= "Size: " . round($stats['cache_size_bytes'] / 1024, 2) . " KB";
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Sanitize debug information to prevent sensitive data exposure
     */
    private static function sanitizeDebugInfo(array $debugInfo): array
    {
        // Remove or mask sensitive information
        if (isset($debugInfo['execution_results'])) {
            foreach ($debugInfo['execution_results'] as &$result) {
                if (isset($result['data'])) {
                    // Mask user IDs and sensitive data
                    if (isset($result['data']['user_id'])) {
                        $result['data']['user_id'] = '***masked***';
                    }
                    if (isset($result['data']['entity_owner'])) {
                        $result['data']['entity_owner'] = '***masked***';
                    }
                    if (isset($result['data']['client_ip'])) {
                        // Only show first 3 octets of IP
                        $ip = $result['data']['client_ip'];
                        $parts = explode('.', $ip);
                        if (count($parts) === 4) {
                            $result['data']['client_ip'] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.***';
                        }
                    }
                }
            }
        }

        // Sanitize page info
        if (isset($debugInfo['page_info'])) {
            // Remove any sensitive page data
            unset($debugInfo['page_info']['sensitive_data']);
        }

        return $debugInfo;
    }
}

/**
 * Interface for custom firewall implementations
 */
interface FirewallInterface
{
    /**
     * Check if access should be allowed
     *
     * @param array $config Firewall configuration from JSON
     * @param array $pageData Complete page data for context
     * @return FirewallResult
     */
    public function check(array $config, array $pageData = []): FirewallResult;
}

/**
 * Result object for firewall checks
 */
class FirewallResult
{
    public bool $allowed;
    public ?string $message;
    public array $data;

    public function __construct(bool $allowed, ?string $message = null, array $data = [])
    {
        $this->allowed = $allowed;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Merge additional data from other firewall results
     */
    public function mergeData(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }
}
