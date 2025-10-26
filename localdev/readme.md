Yore local development: serve and router

Purpose
- Provide a fast, production-like local loop using PHP’s built-in server while preserving Yore’s Apache-style routing and multi-tenant behavior.
- Document the full request flow (routing, tenancy, page/view resolution) and outline likely causes of 404s seen locally but not in production.

Files
- localdev/serve.php: Starts the local server and sets tenant domain.
- localdev/router.php: Emulates Apache rewrite to funnel requests into web/index.php.
- localdev/stop-php-server.php: Convenience script to kill any lingering php -S processes.

Quick start
- From repo root, run: php localdev/serve.php <domain>
  - Example: php localdev/serve.php app.firsthealthhc.com
  - Output: Serving app.firsthealthhc.com at http://localhost:8000
- Visit http://localhost:8000 in your browser. The request will be processed as if Host was app.firsthealthhc.com.
- Optional: export YORE_DOMAIN=<domain> and run php -S localhost:8000 -t web localdev/router.php directly.

What the local server does
- serve.php reads the domain from argv[1] (or prompts), sets the environment variable YORE_DOMAIN, and launches php -S with localdev/router.php as the router.
- router.php mirrors the Apache rewrite used in production (INSTALLING.md):
  - Static files/dirs under web/ are served directly.
  - All other requests are rewritten to web/index.php, with:
    - $_SERVER['PATH_INFO'] = requested path
    - $_SERVER['HTTP_HOST'] and $_SERVER['SERVER_NAME'] overridden to getenv('YORE_DOMAIN') if provided (domain shim for tenancy)

Production parity (Apache)
- In production, Apache is configured (see INSTALLING.md) roughly as:
  - RewriteCond %{REQUEST_FILENAME} !-f
  - RewriteCond %{REQUEST_FILENAME} !-d
  - RewriteRule ^(.*)$ /index.php/$1 [NC,L]
- localdev/router.php replicates this behavior for the PHP built-in server.

How Yore handles routing and multi-tenancy
- Single entry point: web/index.php initializes App\Controller, which extends App\Library.
- Domain (tenant) detection: Library::init() sets $this->domain from $_SERVER['SERVER_NAME'] (router injects YORE_DOMAIN so localhost behaves as your tenant domain). Per-tenant settings are loaded from:
  - pages/_domains/<domain>/modules.json (debug flag, excluded modules)
  - pages/_domains/<domain>/env.json (arbitrary settings; used by modules)
- URL parsing: Library::init() consumes $_SERVER['REQUEST_URI'], normalizes by lowercasing and replacing spaces, hyphens, and periods with underscores, then splits into params:
  - /{site}/{name}/{arg1}/{arg2}/{arg3}
  - Special prefixes:
    - /api/... → API mode; Controller->ApiProcess invokes Modules\\<Name>->api_<action>()
    - /module/... → Module web route; Controller->moduleProcess invokes Modules\\<Name>->web_<action>()
    - /remote/... → Flags remote content mode (reserved for future use)
- Pages and views:
  - Controller::json() loads page JSON (first match wins):
    1) pages/_domains/<domain>/<site>/<name>.json
    2) pages/<site>/<name>.json
     If neither exists → 404.
  - Controller::process() selects the body view (first match wins):
    1) Role Blade:   pages/_domains/<domain>/<site>/views/<role>.blade.php (if $_SESSION['role'] matches)
    2) Role HTML:    pages/_domains/<domain>/<site>/views/<role>.html
    3) Domain Blade: pages/_domains/<domain>/<site>/views/<view>.blade.php
    4) Domain HTML:  pages/_domains/<domain>/<site>/views/<view>.html
    5) Default HTML: pages/<site>/views/<view>.html (usually wraps @body())
- Assembly and rendering:
  - Controller::assemble() requires the theme declared in page JSON: web/themes/<theme>/
    - Includes all CSS then JS from the theme (and global web/css, web/js), then header.php, navbar.php, body content, footer.php.
    - Missing theme assets cause a 500.
  - Controller::view() passes the assembled output through:
    - Blade (for .blade.php views; BladeRenderer uses per-tenant view dir)
    - Fred directive engine (module-provided fred_* methods)

Special case: module routes
- For /module/<Module>/<action> routes, moduleProcess() executes Modules\\<Module>->web_<action>($method) and captures returned HTML in $this->html.
- After executing the module method, Controller resets $this->site = 'default' and $this->name = 'home' and then proceeds to load the page JSON and view to frame the module’s HTML inside the tenant’s default layout. If default/home does not exist, you will see a 404 even if the module method ran correctly.

End-to-end request flow (concise)
1) Browser → localdev server at http://localhost:8000.
2) router.php serves static or rewrites to index.php; injects YORE_DOMAIN into SERVER_NAME/HTTP_HOST.
3) web/index.php starts a 24-hour secure session cookie for the current domain, bootstraps Controller.
4) Library::init():
   - Parse REQUEST_URI (api/module/remote detection, site/name/args)
   - Determine tenant domain from SERVER_NAME
   - Load per-tenant modules/env, autoload modules, call yore_module_init and yore_module_post_init
5) Controller:
   - If API: ApiProcess() → modules api_* → JSON output
   - Else if Module: moduleProcess() → modules web_* → set site=name to default/home
   - Load page JSON → select view → assemble theme → Fred/Blade render → echo page

Why a login page might 404 locally but not in production (ranked by likelihood)
1) Missing default/home for the tenant in localdev (very likely)
   - Module routes wrap their output inside the tenant’s default/home page. If pages/_domains/<domain>/default/home.json (or fallback pages/default/home.json) is missing on your dev machine, Controller::json() returns false → abort(404).
   - Production likely has this page present.
2) YORE_DOMAIN not set or mismatched (likely)
   - If you start the server without providing the tenant domain, SERVER_NAME will be localhost and Library::init() will look under pages/_domains/localhost/... where your tenant content doesn’t exist → 404.
   - Always start with: php localdev/serve.php <your-tenant-domain>
3) Requesting the wrong path for module login (moderate)
   - Yore’s Users module expects /module/users/login (not /users/login). In prod you might have a custom rewrite/alias, but localdev/router.php exactly mirrors the documented Apache rule and will not invent aliases.
   - Verify the path and check that Modules/Users/Module.php defines web_login().
4) Case/normalization differences in the URL (moderate)
   - Library::init() lowercases and converts spaces, hyphens, and periods to underscores. If your page JSON or per-tenant views rely on mixed case or dots in site/page names, local requests might resolve differently than expected.
5) Theme or global asset prerequisites missing (moderate → 500 typically)
   - assemble() requires web/themes/<theme>/css/*.css and js/*.js to exist. If missing locally, you’ll get a 500, not 404—but often this is reported as “the page won’t load.” Ensure the selected theme exists and has assets.
6) Blade view root mismatch for local paths (low to moderate)
   - BladeRenderer’s view root is set to /var/www/yore/pages/_domains/<domain>/<site>/views/ (absolute). If your local checkout isn’t under /var/www/yore, domain Blade views (option 1/3 above) won’t resolve, potentially breaking pages that rely on Blade.
   - Symptoms are usually errors rather than 404, but if your page JSON points to a Blade-only view and the renderer can’t find it, the page may fail. Prefer HTML views or align local pathing.
7) Sessions/cookies on localhost with Secure + SameSite=None (low)
   - web/index.php sets session cookies with secure=true and SameSite=None. On http://localhost, some browsers may decline to set a Secure cookie without HTTPS. This impacts maintaining login state after POST/redirect, not the route existence itself, so it’s unlikely to generate 404s on the login endpoint itself.
8) Domain JSON/view mismatch due to port or subdomain (low)
   - Library uses SERVER_NAME (no port) which router normalizes via YORE_DOMAIN. If YORE_DOMAIN is set wrongly (e.g., missing subdomain), tenancy will point at the wrong folder.

Notes
- localdev/* scripts are for development only and are not used in production.
- The router intentionally avoids altering $_SERVER['REQUEST_URI']; Library::init() reads REQUEST_URI directly, which matches Apache behavior.
