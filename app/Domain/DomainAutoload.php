<?php
declare(strict_types=1);

namespace App\Domain;

/**
 * Runtime PSR-4 mapping for domain-scoped Models.
 *
 * Public convention:
 *   Folder:    /pages/_domains/{domain}/Models/
 *   Namespace: Domain\\{StudlyDomain}\\Models\\
 *
 * Example:
 *   domain = app.yoreweb.com -> StudlyDomain = AppYorewebCom
 *   Namespace prefix => Domain\\AppYorewebCom\\Models\\
 *   Directory         => /pages/_domains/app.yoreweb.com/Models/
 */
class DomainAutoload
{
    /** Convert a domain like app.yoreweb.com or store.example-site.org to AppYorewebCom / StoreExampleSiteOrg */
    public static function studlyDomain(string $domain)
    {
        $domain = strtolower($domain);
        $domain = str_replace(['.', '-'], ' ', $domain);
        $parts  = preg_split('/\s+/', $domain, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts  = array_map(static fn(string $p) => ucfirst($p), $parts);
        return implode('', $parts);
    }

    /**
     * Register a PSR-4 autoload mapping for the current domain's Models folder.
     * If Composer's ClassLoader is present, we add a PSR-4 prefix there.
     * Otherwise, we register a minimal SPL autoloader for just this prefix.
     */
    public static function registerModels(string $domain, string $domainPath)
    {

        $studly  = self::studlyDomain($domain);
        $prefix  = 'Domain\\' . $studly . '\\Models\\'; // e.g Domain\AppYorewebCom\Models
        $dir     = rtrim($domainPath, "\\/\n\r\t ") . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR;

        // Only proceed if the directory exists
        if (!is_dir($dir)) {
            return; // No Models folder for this domain; nothing to do.
        }

        // Try to find the Composer ClassLoader from registered autoloaders
        $loader = null;
        $autoloaders = spl_autoload_functions();
        if (is_array($autoloaders)) {
            foreach ($autoloaders as $fn) {
                if (is_array($fn) && is_object($fn[0]) && $fn[0] instanceof \Composer\Autoload\ClassLoader) {
                    $loader = $fn[0];
                    break;
                }
            }
        }


        try {
            if ($loader instanceof \Composer\Autoload\ClassLoader) {
                // true for prepend so domain models override any existing ones if duplicated
                //dd([$prefix, $dir, true]);
                $loader->addPsr4($prefix, $dir, true); // <-- bad crash here
                return;
            }
        } catch (\Throwable $e) {
            exit($e->getMessage());
        }


        // Fallback: minimal autoloader for this exact prefix
        spl_autoload_register(function (string $class) use ($prefix, $dir): void {
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                return; // not our namespace
            }
            $relative = substr($class, strlen($prefix));
            $file     = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            if (is_file($file)) {
                /** @noinspection PhpIncludeInspection */
                require $file;
            }
        });
    }
}
