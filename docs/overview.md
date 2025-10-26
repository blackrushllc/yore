Yore Framework: Architecture Overview

Purpose and scope
Yore is a lightweight, multi-tenant PHP web framework designed to serve many domains (tenants) from one codebase. It favors simple, explicit conventions over heavy scaffolding: pages are defined by JSON files and rendered with HTML or Blade views; functionality is composed via small, pluggable Modules. The canonical reference implementation in this repository is the App module, the app.yoreweb.com domain, and the app theme.

High-level architecture
- Single front controller: All web traffic enters through web/index.php, which initializes App\Controller.
- One controller, many tenants: Controller extends Library and handles every request for any domain (tenant). The active tenant is inferred from the HTTP host name.
- JSON-driven pages: Page metadata and options live in pages/_domains/\<domain>/\<site>/\<page>.json. Pages render using HTML or Blade views located beside the JSON.
- Themes per tenant: Themes live under web/themes/\<theme> and supply header.php, navbar.php, footer.php and static assets.
- Modules compose features: Each folder in modules/\<ModuleName> with a Module.php is auto-discovered. Modules can add routes (web_*/api_*), provide views, Blade and Fred directives, and expose helpers for other modules.
- CLI parity: app/Command mirrors Controller’s environment for CLI tasks.

Request lifecycle
1) Initialization (Library::init)
- Parse REQUEST_URI into params: /{site}/{name}/{arg1}/{arg2}/{arg3}.
- Special prefixes toggle operating modes:
  - /api/... → API mode (Controller→ApiProcess, modules respond to api_* methods)
  - /module/... → Module route (Controller→moduleProcess, modules respond to web_* methods)
  - /remote/... → Remote content mode flag (reserved for externalized page sources)
- Resolve domain (tenant) from SERVER_NAME (defaults to local for CLI/unknown).
- Load per-domain settings:
  - pages/_domains/\<domain>/modules.json → module toggles (debug, exclude list)
  - pages/_domains/\<domain>/env.json → arbitrary environment settings, e.g., DB credentials for modules.
- Autoload modules from modules/* that contain Module.php, skipping any in the exclude list. For each, call yore_module_init(\$controller, \$myName) and optional yore_module_post_init().

2) Page selection (Controller::json and ::page)
- Discover the page JSON at pages/_domains/\\<domain\>/\<site>/\<name>.json, falling back to pages/\<site>/\<name>.json if present. If not found, 404.
- JSON is decoded into \$this->data. Important keys include:
  - theme: the theme folder under web/themes to use.
  - view: the base view name (defaults to homepage for the default home page).
  - views: optional role-based view mapping/list; if a user role is present in \$_SESSION['role'], a role-named view may be selected.
  - arbitrary page properties (available to views and directives).

3) View resolution (Controller::process)
- Resolution order (first match wins):
  1. Role-specific Blade view: pages/_domains/\<domain>/\<site>/views/\<role>.blade.php
  2. Role-specific HTML view:  pages/_domains/\<domain>/\<site>/views/\<role>.html
  3. Domain Blade view:        pages/_domains/\<domain>/\<site>/views/\<view>.blade.php
  4. Domain HTML view:         pages/_domains/\<domain>/\<site>/views/\<view>.html
  5. Default HTML template:    pages/\<site>/views/\<view>.html (typically a wrapper that yields @body())
- If no content path resolves, Controller aborts with 404. Otherwise, \$this->html is populated with the body content for the page.

4) Theming and assembly (Controller::assemble)
- Compute CSS/JS includes: first theme assets (web/themes/\<theme>/css/*.css, js/*.js), then global assets (web/css, web/js). Missing theme assets cause a 500.
- Buffer and concatenate theme/html/header.php, theme/html/navbar.php, the resolved body HTML/Blade output, then JS includes, then theme/html/footer.php.
- The final stack is then passed through the rendering pipeline.

Rendering pipeline
- Blade: If a Blade view is selected, it is rendered by app/BladeRenderer via Jenssegers\Blade. The renderer registers module-provided Blade directives (module method blade_directives) and can translate select fred_* methods as directives.
- Fred: All output is passed through the Fred directive engine (App\Fred\Fred), which processes @name(...) style directives. Modules can expose fred_* methods that Fred can invoke for dynamic content inside HTML or Blade templates.

Modules at a glance
- Structure: modules/\<Name>/Module.php extends App\Modules. Optional Traits can split responsibilities (e.g., Web routes, API routes, Fred/Blade directives).
- Settings & views: Module default settings live alongside the module (settings.json); per-tenant overrides live in pages/_domains/\<domain>/modules/\<Name>/settings.json. Similarly, module views can be overridden per-tenant at pages/_domains/\<domain>/modules/\<Name>/views/.
- Routing: /module/\<name>/\<action> → invokes Modules\\<Name>\Module::web_\<action>(\$method). In API mode (/api/\<name>/\<action>), Modules\\<Name>\Module::api_\<action>(\$method) is invoked. Your method returns HTML (for web) or data (for API).

Tenancy example (canonical)
- Domain: app.yoreweb.com (pages/_domains/app.yoreweb.com)
- Theme: app (web/themes/app)
- Module: App (modules/App)
This trio demonstrates recommended practices: clean page JSONs, role-based views, per-domain module overrides, and minimal logic in views.

Debugging and DX
- Toggle debug per domain in modules.json: { "debug": true }. Also, ?debug=1 can activate extra output.
- In debug mode, Blade cache is cleared per request; the controller emits diagnostic output on errors.

What Yore is optimized for
- Multi-tenant apps that want explicit, file-based configuration
- Teams that prefer “dumb” templates and small feature modules
- Rapid extension via Modules, with per-domain overrides for behavior and look

Related references
- README.md for quickstart and directory guide
- MULTI_TENANCY.md, DATABASE.md, FIREWALL.md for deeper topics
- .yunie/guidelines.md for conventions and expectations