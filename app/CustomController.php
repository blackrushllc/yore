<?php
namespace App;

/**
 * Base class for domain/page-specific controllers.
 * Users can extend this class in /pages/_domains/<domain>/<firstSlug>/Controller.php
 * to implement web_ and api_ handlers.
 */
abstract class CustomController
{
    /** @var string|null */
    protected $controllerStatus = null;

    /** @var string|null */
    protected $controllerName = null;

    /** @var \App\Controller|null Reference to the core Controller */
    protected $controller = null;

    // Page metadata (equivalent to keys usually found in page JSON)
    protected ?string $domain = null;
    protected ?string $site = null;
    protected ?string $page = null;
    protected ?string $title = null;
    protected ?string $theme = null;
    protected ?string $desc = null;
    protected $security = null; // mixed to allow future structures
    protected $public = null;   // bool|null

    /**
     * Base constructor so child controllers can call parent::__construct().
     * We intentionally do not require the core Controller instance here.
     */
    public function __construct()
    {
        // no-op; properties are set by child constructors
    }

    /**
     * Initialize the custom controller with the core Controller instance.
     * Allows access to modules, database, mail, etc.
     * Also applies any metadata provided by the child to the core controller's data object.
     *
     * @param Controller|null $controller
     * @return static
     */
    public function yore_controller_init($controller = null)
    {
        $this->controller = $controller;
        $this->controllerStatus = 'Running';
        $this->controllerName = static::class;

        // Apply metadata to the main controller if provided
        if ($this->controller) {
            if (!is_object($this->controller->data)) {
                $this->controller->data = (object)[];
            }
            $meta = $this->collectMeta();
            foreach ($meta as $k => $v) {
                // Only set provided values; allow core fallbacks to fill in missing fields
                $this->controller->data->$k = $v;
            }
        }
        return $this;
    }

    /**
     * Collect non-null metadata values from this controller.
     * @return array<string,mixed>
     */
    protected function collectMeta(): array
    {
        $meta = [
            'domain' => $this->domain,
            'site' => $this->site,
            'page' => $this->page,
            'title' => $this->title,
            'theme' => $this->theme,
            'desc' => $this->desc,
            'security' => $this->security,
            'public' => $this->public,
        ];
        // Filter out null values only (keep false/0/"0")
        return array_filter($meta, static function ($v) {
            return $v !== null;
        });
    }
}
