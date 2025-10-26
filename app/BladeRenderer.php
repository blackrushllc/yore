<?php

namespace App;

use Jenssegers\Blade\Blade;

class BladeRenderer
{
    private Blade $blade;

    private $controller, $cachePath;

    public function __construct(
        string $viewsPath,
        string $cachePath,
        array $globals = [],
        $container = null
    ) {
        $this->controller = $globals['controller'] ?? null;
        $this->cachePath = $cachePath ?? null;

        $this->blade = new Blade($viewsPath, $cachePath, $container);

        // Share global data (available in all templates)
        foreach ($globals as $k => $v) {
            $this->blade->share($k, $v);
        }

        // Register custom directives only after Blade is fully initialized
        $this->registerCustomDirectives();
    }

    private function registerCustomDirectives(): void
    {
        // Example: add a couple custom directives
        $compiler = $this->blade->compiler();

        // @author($expr)
        $compiler->directive('author', function ($expr) {
            return "<?php echo 'Erik Olson'; ?>";
        });

        // @iif($expr, $trueValue, $falseValue) - THIS DOES NOT WORK:
        // It seems that the Blade compiler does not support multiple parameters in custom directives directly.
        // Uncaught ArgumentCountError:
        // Too few arguments to function App\BladeRenderer::App\{closure}(),
        // 1 passed and exactly 3 expected in /var/www/yore/app/BladeRenderer.php:42

        $compiler->directive('iif', function ($expr) {
            return "<?php echo $expr[0] ? $expr[1] : $expr[2]; ?>";
         });

        // This would need to be handled by the Fred class

        // Do any actual Blade directives support multiple parameters inside of the parenthesis?


        // @datetime($ts)
        $compiler->directive('datetime', function ($expr) {
            return "<?php echo (new DateTime($expr))->format('Y-m-d H:i'); ?>";
        });


        // @date($ts)
        $compiler->directive('date', function ($expr) {
            return "<?php echo (new DateTime($expr))->format('Y-m-d'); ?>";
        });

        // @asset('css/app.css')
        $compiler->directive('asset', function ($expr) {
            return "<?php echo htmlspecialchars('/assets/' . trim($expr, \"'\\\"\"), ENT_QUOTES, 'UTF-8'); ?>";
        });

        // Iterate through each loaded module and call its blade directives method if it exists
        if ($this->controller && isset($this->controller->modules) && is_array($this->controller->modules)) {
            foreach ($this->controller->modules as $module) {
                if (method_exists($module, 'blade_directives')) {
                    $module->blade_directives($compiler);
                }
            }

            // Iterate through each loaded module and generate blade directives from methods that start with "fred_"
            foreach ($this->controller->modules as $module) {
                $reflection = new \ReflectionClass($module);
                foreach ($reflection->getMethods() as $method) {
                    if (strpos($method->name, 'fred_') === 0) {
                        $directiveName = substr($method->name, strlen('blade_'));
                        $compiler->directive($directiveName, function ($expr) use ($module, $method) {
                            return "<?php echo \$this->controller->modules['{$module->myName}']->{$method->name}({$expr}); ?>";
                        });
                    }
                }
            }
        }
    }

    public function render(string $view, array $data = []): string
    {
        if($this->controller->is_debug) {
            $this->clearCache();
        };

        return $this->blade->render($view, $data);
    }

    public function share(string $key, mixed $value): void
    {
        $this->blade->share($key, $value);
    }

    public function make(): Blade
    {
        return $this->blade;
    }

    public function clearCache(): void
    {
        if (is_dir($this->cachePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->cachePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                $file->isDir()
                    ? @rmdir($file->getPathname())
                    : @unlink($file->getPathname());
            }
        }
    }
}
