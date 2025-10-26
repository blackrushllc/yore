Modules in Yore: Creating and Registering Features

What a Module is

A Module is a small, focused feature packaged under modules/<Name> with a Module.php entry class. Modules are auto-discovered and initialized on every request (unless excluded for the active tenant). Modules may:
- Expose web routes (/module/<name>/<action>) via web_* methods
- Expose API routes (/api/<name>/<action>) via api_* methods
- Provide views and settings (with per-tenant overrides)
- Register Blade directives and provide Fred directives (fred_* methods)
- Cooperate with other modules via the controller->modules array

Directory layout
- modules/
  - <Name>/
    - Module.php (required)
    - settings.json (optional; default settings)
    - views/ (optional; module-provided HTML/Blade templates)
    - Traits/ (optional; split responsibilities, e.g., WebTrait, ApiTrait, FredTrait)

Minimal Module.php

Use this as a starting point (adapt the namespace and class name):

namespace Modules\Hello;

use App\Modules as BaseModule;
```
class Module extends BaseModule {
    public function yore_module_init(\$controller = null, \$myName = null) {
        parent::yore_module_init(\$controller, \$myName);
        // Read per-tenant env if needed: \$controller->settings
        // Read module settings (defaults or tenant override): \$this->settings
    }

    // Web route: /module/hello/ping (GET|POST)
    public function web_ping(\$method) {
        return "<h1>Hello from web_ping (\$method)</h1>";
    }

    // API route: /api/hello/ping (GET|POST)
    public function api_ping(\$method) {
        return [ 'ok' => true, 'method' => \$method ];
    }

    // Optional: Blade directives
    public function blade_directives(\$compiler) {
        \$compiler->directive('hello', function (\$expr) {
            return "<?php echo 'Hello ' . (\$expr ?? 'World'); ?>";
        });
    }

    // Optional: Fred directive usable as @hello2("World") in HTML/Blade
    public function fred_hello2(\$name = 'World') {
        return "Hello \$name";
    }
}
```
How Yore loads modules
- On each request, Library::init scans modules/* for a Module.php. For every match, the class Modules\<Name>\Module is included.
- The framework instantiates each class and calls yore_module_init(\$controller, \$myName). The \$controller grants access to the active tenant, page data, request params, and other modules: \$controller->modules['Users'], etc.
- If pages/_domains/<domain>/modules.json contains an exclude list, those modules are skipped. If debug is enabled, extra diagnostics are available and Blade cache is cleared per request.

Defining routes
- Web routes: Implement public function web_<action>(\$method) in your Module. Access via /module/<Name>/<action>. The \$method is the HTTP method (GET or POST).
- API routes: Implement public function api_<action>(\$method). Access via /api/<Name>/<action>. Return arrays or scalars; the Controller JSON-encodes API responses.
- Page routes: Standard page URLs use /{site}/{name}/... and are resolved by page JSON and views (not by module methods). Modules can still influence page rendering via directives or by writing to \$controller state.

Views and settings (with per-tenant overrides)
- Default views: Place reusable module views under modules/<Name>/views/ and reference them from module methods via \$this->viewFilePath('file.html').
- Tenant view overrides: Place overrides under pages/_domains/<domain>/modules/<Name>/views/<file>. These take precedence over packaged views.
- Default settings: Define modules/<Name>/settings.json and read it via \$this->settings (automatically loaded during yore_module_init).
- Tenant settings overrides: pages/_domains/<domain>/modules/<Name>/settings.json override the default file and are also available via \$this->settings.
- Tenant env: Read cross-module, tenant-scoped environment values from \$this->controller->settings (parsed from env.json in the active tenant folder).

Working with Blade and Fred
- Blade: Register custom directives in blade_directives(\$compiler). Your directives become available to all Blade templates.
- Fred: Any public method named fred_<name>(...) can be invoked from HTML/Blade via @<name>(args). Use this for small, embed-friendly dynamic fragments.

Accessing other modules
- All loaded modules are available in \$this->controller->modules. For example, \$this->controller->modules['Users'] gives you the Users module instance.
- Keep cross-module calls simple and avoid tight coupling; treat them as optional capabilities.

Recommended practices
- Keep Module.php small; split routes and directives into Traits/ when the module grows (e.g., WebTrait.php, ApiTrait.php, FredTrait.php).
- Keep views dumb; put business logic into Modules. Prefer directives over inline PHP in templates.
- Read secrets/connection info from env.json via \$controller->settings, not from code.
- Support tenancy by honoring per-tenant view/settings overrides where applicable.

Public examples to consult
- App (canonical end-to-end example with app.yoreweb.com)
- Users, Debug, Mail, Database, Hello, Library, Skeleton