# Yore Framework – Project Guidelines

## What Yore is

Yore is a PHP multi-tenant web framework used for building web applications. 
It serves different domains (tenants) from a single
codebase with per-domain views and settings, globally available modules, and a CLI for maintenance tasks.

## What Yore is not

- A CMS.
- A pretty website generator.
- Wordpress
 

## Repo map (authoritative)
- '/.yunie/' - Yore AI assistant configuration files.
- `/app/` – Core framework code (Controller, Command, Library, BladeRenderer, Fred, etc).
- `/app/Modules.php` – Base class for all modules.
- `/app/Crud/` – CRUD generator and base classes.
- `/app/Domain` - Runtime PSR-4 mapping for domain-scoped Models.
- `/app/Fred` - Fred
- `/app/BladeRenderer.php` - Blade rendering engine wrapper.
- `/app/Controller.php` - The main Web Controller class, extends Library.
- `/app/Command.php` - The main console command class, extends Library.
- `/app/Library.php` - A base class for all core framework classes.
- `/localdev/` - Setting up a local development environment.
- `/docs/` - AI onboarding docs (for AI assistants to understand Yore)
- `/modules/` – Feature modules (each has its own `Module.php` entry and methods may be subdivided into Traits).
- `/modules/*/Traits` – Larger modules are split into Traits. Typically for Web (routes), API (routes), Fred (view directives), and Framework-centric methods like processing form posts, running scheduled tasks, etc.
- `/web` – This is the public-facing code, index.php and assets.
- `/web/themes` – Theme packs (“A.K.A. dumb themes”); Usually a site is associated with a theme pack.
- `pages/_domains/<domain>/env.json` – This contains the environment variables for a domain, e.g. database credentials, etc.
- `pages/_domains/<domain>/modules.json` – This contains the list of modules enabled or disable for a domain, and some other settings like Debug Mode.
- `pages/_domains/<domain>/default` – This is the top level pages for a domain (i.e. domain.com/).
- `pages/_domains/<domain>/default/*.json` – These files define all pages in the top level folder of the domain (i.e. admin.json, guest.json, home.json, etc).
- `pages/_domains/<domain>/default/views/` – Domain-specific Blade/Twig-like views for the top level pages.
- `pages/_domains/<domain>/*` – These are sub-pages for a domain. (i.e. domain.com/about/ or domain.com/about/team/)
- `pages/_domains/<domain>/*/*.json` – These files define all pages in this sub-folder of the domain (i.e. /about/this /about/that /about/team/ etc). 
- `pages/_domains/<domain>/*/views/` – Domain-specific Blade/Twig-like views for the sub-pages.
- `pages/_domains/<domain>/modules/*/views/` – Domain-specific Blade/Twig-like views to override module views (i.e. custom login page, error pages, etc).
- `pages/_domains/<domain>/modules/*/settings.json` – Domain-specific module setting overrides (i.e. mail or database configs, etc).
- `pages/_domains/<domain>/settings.php` – Domain settings (routes, features).
- `pages/_domains/<domain>/Models` – All ORM Model Classes for a domain.
- `pages/_domains/<domain>/_models` – alternate location for Models 
- `pages/_domains/<domain>/Modules` – Optional module overrides (Settings, Views, etc) for modules used by a domain. 
- `pages/_domains/<domain>/modules` – Alternate location for Modules
- `pages/_domains/<domain>/_modules` – Alternate location for Modules 
- `pages/_domains/<domain>/*/Controller.php` – Optional controllers for a domain's routes  
- `pages/_domains/<domain>/` – 
- `public/` – Front controller + public assets.
- `cli.php` – CLI entry for maintenance tasks.
- `yore` – Bash helper for common commands.

## Glossary
- "Module" is a Php class that implements a custom feature and extends the functionality of the framework.
- "Domain" is a tenant where an entire website is served by the framework. There can be many domains.
- "View" is a single html or Php file representing the body of one page of a domain, or the body of a specific route defined in a module
- "Blade" is a template based on the Laravel framework.
- "Twig" is a template based on the Twig framework (not currently implemented).
- "Theme" is a collection of Php pages (header, footer and navbar) along with Javascript, CSS and image assets.
- "Page Json" is a single json file representing one page of a domain.
- "Site Data" is a bundle file that can be exported containing the contents of a domain or all domains and stored in a variety of ways (i.e. database, S3, FTP, etc).
- "Site Json" is a Json file that contains the exported Site Data
- "Fred" is a view directive system that has built in functions and allows modules to define custom functions that can embedded in views (like Wordpress shortcodes).
- "Fred" stands for Framework Embedded Directive and is based on the "Framework Fred" language from the 1980's
- "Section" refers to the first slug of a domain (i.e. domain.com/about/ where "about" is the section slug/)  
- "Pagekey" refers to the second slug of a domain (i.e. domain.com/about/team where "team" is the Pagekey slug/)
- "Arg1", "Arg2" and "Arg3" are the 3rd, 4th and 5th slugs of a domain (i.e. domain.com/about/team/john/doe where "john" is the Arg1 slug, "doe" is the Arg2 slug and the Arg3 slug is empty)
- "Route" is a url that can be used to access a page of a domain, or a function defined on a module.
- "Debug" is a mode where the framework will output additional information to the browser.
- "Yore" is a callback to a simpler time when things were not so complicated.
 
## Caveats

- Every page has a .json file that defines the page.
- Every page must have at least one view file associated with it.
- View files can be html or Laravel "blade" (or "twig", etc if you want to support it).
- `pages/_domains/<domain>/default/home.json` is reserved for the default home page. It may contain a `view` key that is different file name than `home.blade.php` or `home.html`.
- `pages/_domains/<domain>/default/views/` is reserved for the default home page views and home.json may contain multiple view names based on the user's role (i.e. 'admin.html' or 'guest.blade.php')
- `pages/_domains/<domain>/*/home.json` is reserved for the home page of a sub-folder. It may contain a `view` key that is different file name than `home.blade.php` or `home.html`. 
- `pages/_domains/<domain>/*/any-page-pagekey.json` will be used for any page pagekey that is not `home.json` and can reference any view in the views folder. 
- `pages/_domains/<domain>/*/views/` may contain many views which are referenced by the json files above. 
- The page .json files may contain a `view` key that references a view file in the views folder.
- The page .json files may contain a `views` key lists a number of views available to render based on the user's role (i.e. admin.html, guest.blade.php, etc).
- The page .json file can contain any number of settings which can be used by the Php modules, html views, blades views, or javascript code while rendering the page.
- There is one web controller class based called Controller which extends the Library class.
- There is one CLI controller class based called Command which extends the Library class.
- All modules that are not excluded for a domain are initialized for every request to that domain.
- Modules may define routes, views, blade directives, Fred directives and other functionality.
- You can activate debug mode on the fly by adding `?debug=1` to the end of the url.
- use `yore cli <command>` to run CLI commands.
- Each module may define a cron method that is called automatically by the scheduler.
- Break medium to large modules into Traits for easier maintenance.
- Yore is not a very secure framework. It is still in development so do not use it for anything important (wink).

## Coding expectations

- **Where to place code**: framework logic → `modules/`; presentation → `pages/_domains/...`; tenant overrides → `pages/_domains/...`.
- Public facing code should be in `web/`.
- **Never** put business logic in `web/`.
- Each page of a domain must have a corresponding `.json` file.
- Each page of a domain must have at least one view file.
- Each page of a domain will reference a theme in the .json file.
- Views may be `.blade.php` or `.html`.
- Views may be overridden per-domain in `pages/_domains/<domain>/.../views/`.
- Views may be overridden per-module in `pages/_domains/<domain>/modules/<module>/views/`.
- Modules may define routes, views, blade directives, Fred directives, and other functionality.
- Use Fred directives for embedding dynamic content in views.
- Fred directives can be used in both html and blade views.
- Use Blade for templating and layout inheritance, and for embedding Php code in views when necessary.
- **Never** put business logic in templates (wink).
- Every module has access to the public members of every other module via the $Controller->modules array.
- Use dd($var) or dd([$var1, $var2]) to dump variables for debugging and end execution of the framework code.
- Use debug mode to see a dump of the Controller class and other data at the bottom of each page.
- Update cli.php and use `yore cli <command>` to add CLI commands that require Yore Classes like Controller or Command.
- Update the `yore` bash helper to add CLI commands that do not require Yore
 

## Tests & running locally

- Unit tests: Not currently implemented.
- Local run: `php -S 127.0.0.1:8080 -t web`.
- Php CLI: `./yore cli <command>` (Junie may call this for commands that require Yore Classes like Controller or Command).
- Bash CLI: `./yore <command>` (Junie may call this for commands that do not require Yore).

## Tasks Junie may perform

- “Create a new module named X; expose route Y; add views under the Z domain; create or update docs.”
- “Refactor logic out of theme into a module; keep templates dumb.”
- “Generate/upgrade tenant scaffolding for a new domain folder.”
- Execute the CRUD process on a table named A in the currently connected database for Domain X, section Y (page names Z are pre-determined by the CRUD generator, i.e. index, edit, add, delete, etc)
- Modify the currently opened view to build a page that does .. (fill in the blank)
- Add CSS, Javascipt, or Php as needed to the currently opened view in order to .. (fill in the blank)
- Export the site data for Domain X to a Site Json file.
- Import the site data for Domain X from a Site Json file.
- “Run the cron jobs for all modules.”
- “List all modules and their status for Domain X.”
- “List all routes defined by modules for Domain X.”
- “List all views available for Domain X.”
- “List all blade directives defined by modules for Domain X.”
- “List all Fred directives defined by modules for Domain X.”
- “Activate debug mode for Domain X.”
- “Deactivate debug mode for Domain X.”

## Style

- PHP 8.x; strict types where feasible.
- Use dependency injection; avoid globals/singletons inside modules.
- Keep functions ≤ ~100 LOC; classes focused & cohesive.

## References

- `INSTALLING.md` – Installing and configuring Yore.
- `README.md` – Overview and quickstart, purpose, directory structire, files of note, and installation of Yore 
- `FIREWALL.md` – A flexible, hierarchical firewall system for controlling page access with multiple configurable rules. Supports multi-tenancy, caching, and comprehensive debugging capabilities.
- `DATABASE.md` - Documentation for the database system used by Yore.
- `MULTI_TENANCY.md` - Documentation for the multi-tenancy system used by Yore.
- `ROLES_AND_PERMISSIONS.md` - Documentation for the roles and permissions system used by Yore.
- `XDEBUG.md` - Xdebug Setup Guide for Yore Framework
- `AI_ONBOARDING.md` - Notes and suggestions on creating a tiny “AI onboarding pack” for Yore, which has not been implemented beyond this document you are reading.
- `/web/document.txt` - An outline for a future comprehensive user guide and fantasy wish list of unrealized features that may bever happen for Yore. 
- `/web/document.htm` - An outline for a future comprehensive user guide and fantasy wish list of unrealized features that may bever happen for Yore. 
