Tenancy in Yore: Domains, Folders, and Settings

Overview

Yore serves many domains (tenants) from one codebase. The active tenant is determined from the HTTP host (SERVER_NAME). Each tenant keeps its pages, views, and per-tenant overrides under pages/_domains/\<domain>. The canonical example is app.yoreweb.com.

How a request maps to a tenant and page
- Tenant (domain): Taken from the request Host header. If unknown or when running CLI/cron, Yore uses the local tenant.
- Path parameters: /{site}/{name}/{arg1}/{arg2}/{arg3}
  - site is the first segment (default when missing)
  - name is the page (home when missing)
  - arg1–arg3 are optional extra segments available to modules or views
- Module and API routes:
  - /module/\<Module>/\<action> → invokes a module web_\<action>() method
  - /api/\<Module>/\<action> → invokes a module api_\<action>() method

Tenant folder layout
All tenant-specific files live beneath pages/_domains/\<domain>:
- env.json — Arbitrary, tenant-specific environment settings accessible as \$controller->settings (e.g., DB host, username, passwords, API keys). Modules should read from here rather than hardcoding secrets.
- modules.json — Enables per-tenant behavior:
  - debug: true|false — turns on verbose behavior and disables Blade caching per request
  - exclude: ["ModuleA", "ModuleB", ...] — list of modules to skip for this tenant
  - (optional) other keys may be added for future behavior; Yore currently reads debug and exclude.
- default/ — The top-level site for “/”. Contains home.json and its views.
- \<site>/ — Additional sites (e.g., /users, /reports, /emails). Each site contains:
  - \<page>.json — page configuration; see below.
  - views/ — HTML or Blade templates used by that site’s pages (may also include role-specific views).
- modules/\<Module>/ — Per-tenant module overrides:
  - settings.json — override a module’s default settings for this tenant
  - views/ — override a module’s packaged views for this tenant

Page JSON schema (practical keys)
- domain: informational; typically the folder name (e.g., app.yoreweb.com)
- site: the site folder that contains this page (e.g., default, users, reports)
- page: the page name (file stem of this JSON)
- title: title used by the theme/view
- theme: required; the theme folder under web/themes to use (e.g., app)
- desc, body: free-form content available to templates and directives
- view: primary view name for this page (e.g., homepage, login)
- views: optional list or mapping enabling role-based views (see below)
- other arbitrary keys: safely available to views and directives via \$data

View lookup and role-based rendering
Controller::process selects the best matching view in this order:
1) Role-specific Blade view: pages/_domains/\<domain>/\<site>/views/\<role>.blade.php
2) Role-specific HTML view:  pages/_domains/\<domain>/\<site>/views/\<role>.html
3) Page Blade view:          pages/_domains/\<domain>/\<site>/views/\<view>.blade.php
4) Page HTML view:           pages/_domains/\<domain>/\<site>/views/\<view>.html
5) Default template:         pages/\<site>/views/\<view>.html
- Role detection: If \$_SESSION['role'] is set and views is present in the page JSON, a view named after that role can be selected (e.g., admin.blade.php).
- If none match, the request aborts with a 404.

Themes per tenant
- Themes are stored in web/themes/\<theme>. A theme provides:
  - html/header.php, html/navbar.php, html/footer.php
  - css/*.css and js/*.js assets
- Page JSON must specify theme. Controller::assemble concatenates header → CSS includes → navbar → page body → JS includes → footer.

Per-tenant overrides for Modules
- Settings override precedence: pages/_domains/\<domain>/modules/\<Module>/settings.json → modules/\<Module>/settings.json.
- View override precedence for module views: pages/_domains/\<domain>/modules/\<Module>/views/\<file> → modules/\<Module>/views/\<file>.
- Modules access the active tenant context and settings via \$this->controller inside Module methods.

Debug mode and local tenant
- Enable per-tenant debug in modules.json: { "debug": true }. You can also append ?debug=1 in the URL for on-the-fly diagnostics.
- The local tenant acts as the default when no matching domain folder exists, or when running CLI/cron. Place its files under pages/_domains/local/.

Canonical example
- Domain: pages/_domains/app.yoreweb.com
- Theme: web/themes/app
- Example modules with public parity: Users, Debug, Mail, Database, Hello, Library, Skeleton (App is the end-to-end reference).