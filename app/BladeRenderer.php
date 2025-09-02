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
        array $globals = []
    ) {
        $this->controller = $globals['controller'] ?? null;
        $this->cachePath = $cachePath ?? null;

        $this->blade = new Blade($viewsPath, $cachePath);

        // Share global data (available in all templates)
        foreach ($globals as $k => $v) {
            $this->blade->share($k, $v);
        }

        // Example: add a couple custom directives
        $compiler = $this->blade->compiler();

        // @datetime($ts)
        $compiler->directive('datetime', function ($expr) {
            return "<?php echo (new DateTime($expr))->format('Y-m-d H:i'); ?>";
        });

        // @asset('css/app.css')
        $compiler->directive('asset', function ($expr) {
            return "<?php echo htmlspecialchars('/assets/' . trim($expr, \"'\\\"\"), ENT_QUOTES, 'UTF-8'); ?>";
        });
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
