# THESE ARE NOTES - DON'T TRUST THIS YET LOL


Make any AI (ChatGPT, Roo Code, Copilot++, etc.) useful on **Yore**. 

Create a tight “onboarding pack” and wire your editor/agent to *always* load that context 
before touching code.

Here’s how we do that:

# 1) Create a tiny “AI onboarding pack” in your repo

Put these files in `/docs/ai/` and keep them short (1–2 pages each). AI does *much* better with 6–10 focused docs than one giant tome.

**Files to add**

1. `00-yore-overview.md` — High-level purpose & mental model

  * One codebase → multi-domain, multi-tenant sites
  * Directory map (see below)
  * Request lifecycle (bootstrap → domain resolve → route → controller/Module → Blade render)
2. `01-architecture.md` — Components & data flow

  * `modules/<name>/Module.php` role, service boundaries
  * `pages/_domains/<domain>/...` and theme/template resolution
  * Where config/env/domain mapping lives
3. `02-conventions.md` — Naming & layout rules AI must follow

  * Class/file naming, PSR rules, where views live, how partials are named
  * jenssegers/blade usage patterns (sections, layouts, components)
4. `03-cli.md` — Your **`yore`** dispatcher & common tasks

  * Exact CLI verbs you support, examples, idempotence expectations
5. `04-modules.md` — How a module is structured

  * Required files, constructor contracts, how to register hooks/routes
  * Example minimal `Module.php` with comments
6. `05-routing.md` — How Yore decides what code runs

  * Domain → site mapping → route → controller/Module method
  * Middleware/pipeline equivalents if any
7. `06-views.md` — Blade usage specific to Yore

  * Layouts, `@include` patterns, shared variables, asset pipeline
8. `07-database.md` — Storage philosophy

  * When to use Eloquent vs raw DB; migrations conventions
9. `08-examples.md` — **Two golden examples**

  * (A) “Hello site” domain + page + module from scratch
  * (B) A real feature (e.g., CSV import flow) with CLI + web + view
10. `99-glossary.md` — Your vocabulary

* “domain theme,” “template,” “module,” “runner,” “tenant,” etc.

**Bonus (AI superfood)**

* `repo-map.yml` — A terse tree showing only important dirs/files; AI loves this index.
* `coding-standards.md` — Do/Dont table (see §5).
* `pitfalls.md` — “Things that look right but break Yore.”

---

# 2) Give AI a structured repo map (copy/paste)

Create `/docs/ai/repo-map.yml`:

```yaml
app_root: .
important_paths:
  bootstrap: bootstrap/
  public: public/
  config: config/
  storage: storage/
  routes: routes/        # if Yore has its own routing config, note where
  modules: modules/      # each subdir contains a Module.php entry point
  pages: pages/          # site pages and per-domain overrides
  templates: templates/  # shared layouts/partials for blade
  pages_domains: pages/_domains/
  cli_entry: cli.php
  dispatcher: yore       # bash helper script
views_blade:
  engine: jenssegers/blade
  layouts: templates/layouts/
  partials: templates/partials/
  components: templates/components/
yore_concepts:
  - multi-domain, multi-tenant from single codebase
  - domain -> site config -> route -> module/controller -> blade render
  - modules provide features, templates provide look/feel
```

---

# 3) Seed prompt for any AI agent (paste into its “project rules”)

Create `/docs/ai/agent-seed.md` and keep it always-loaded:

```
You are the AI assistant for the Yore framework codebase.

Core rules:
- Follow the docs in /docs/ai/*.md strictly. If missing info, propose a minimal addition in the same style.
- Prefer small, reviewable diffs. Explain why each change is needed.
- Never relocate files that affect domain resolution without updating /docs/ai/repo-map.yml.
- Keep Blade conventions: layouts live under templates/layouts/, partials under templates/partials/.
- When generating new features, produce:
  (1) module skeleton under modules/<feature>/ with Module.php
  (2) route/entry glue consistent with Yore routing
  (3) views under pages/ or templates/ with domain-aware overrides
  (4) optional yore CLI tasks for batch ops
- Use jenssegers/blade patterns (sections, components) consistent with existing templates.
- Add or update brief docs when you add a new concept (1-2 paragraphs, max).

Output format:
- First: a minimal plan (bulleted)
- Then: diffs or files with paths
- Then: quick test steps (CLI + browser)
- Then: rollback instructions
```

If you use Roo Code (or any IDE agent), set this as its **default project memory** or “always include” context.

---

# 4) Minimal golden examples the AI can imitate

In `08-examples.md`, include tiny, correct starting points. For example:

**`modules/Hello/Module.php`**

```php
<?php

namespace Modules\Hello;

class Module
{
    public function register(): void
    {
        // Register routes/endpoints for this module (pseudo-code if Yore uses custom router)
        // Router::get('/hello', [$this, 'show']);
    }

    public function show(array $ctx = [])
    {
        // Return data to the Blade view
        return [
            'title' => 'Hello from Yore',
            'message' => 'It works!',
        ];
    }
}
```

**`templates/layouts/app.blade.php`**

```php
<!doctype html>
<html>
  <head>
    <title>@yield('title', 'Yore')</title>
  </head>
  <body>
    <header>@include('templates.partials.nav')</header>
    <main>@yield('content')</main>
  </body>
</html>
```

**`pages/home.blade.php`**

```php
@extends('templates.layouts.app')

@section('title', $title ?? 'Home')

@section('content')
  <h1>{{ $message ?? 'Welcome' }}</h1>
@endsection
```

**CLI helper sample in `03-cli.md`**

```bash
# Examples
./yore cli module:list
./yore cli module:make Blog
./yore cli domain:add example.com
./yore cli cache:clear
```

These are short, idiomatic, and make pattern-learning easy.

---

# 5) A simple Do / Don’t table (AI follows these well)

Add to `coding-standards.md`:

| Do                                               | Don’t                                                 |
| ------------------------------------------------ | ----------------------------------------------------- |
| Keep new feature diffs < 200 lines when possible | Rename or move core directories without updating docs |
| Add a doc snippet when introducing a new concept | Mix Blade layout and partial responsibilities         |
| Prefer clear, boring names (`Users`, `Invoices`) | Over-abstract with “ManagerFactoryResolver”           |
| Use domain-aware layouts under `pages/_domains/` | Hardcode domain logic inside modules                  |
| Use `yore` CLI for batch ops                     | Hide behavior in ad-hoc scripts outside CLI           |

---

# 6) Roo Code / VS Code setup (if you’re using it)

* Configure its “Memory/Project Context” to always load:

  * `/docs/ai/agent-seed.md`
  * `/docs/ai/repo-map.yml`
  * `/docs/ai/00-*.md` through `/docs/ai/08-*.md`
* Create a “**Yore – New Feature**” task template in the agent:

  1. Read repo-map and conventions
  2. Propose minimal plan (module, routes, views, optional CLI)
  3. Generate diffs/files
  4. Provide test + rollback steps
* Turn off “auto-approve everything”; allow auto-approve for **doc updates** and **non-critical template additions** only.
* Save a **“Refactor safely”** recipe: “Refactor only within module boundary X; do not touch routing/domain resolution; keep Blade contracts.”

---

# 7) Make it Retrieval-Augmented (optional but powerful)

If you want ChatGPT to read long docs without pasting them every time:

* Put the docs in a small wiki or site (even local) and give the model a *brief* summary + pointers.
* Or keep it simple: paste the needed doc snippets per task; keep each file short so pastes are painless.

---

# 8) Keep it evergreen with a one-minute PR checklist

When you merge changes that alter how Yore works, **update**:

* `repo-map.yml`
* Any affected `0x-*.md` doc (1–3 lines is fine)
* `pitfalls.md` if you hit a new gotcha
* Add an example (if the change introduces a new pattern)

---

## Quick starter you can copy now

Create these files and paste the snippets:

* `/docs/ai/00-yore-overview.md` — 6–10 bullets describing purpose, request flow, directory map
* `/docs/ai/repo-map.yml` — the YAML above
* `/docs/ai/agent-seed.md` — the seed prompt above
* `/docs/ai/02-conventions.md` — Blade rules, naming, PSR bits
* `/docs/ai/03-cli.md` — list your real `yore` commands
* `/docs/ai/08-examples.md` — the 2 golden examples

Once those exist, tell your AI agent:

> “Before any change, read `/docs/ai/*.md` + `/docs/ai/repo-map.yml`. Confirm understanding, propose a minimal plan, then produce diffs.”

---

Spin up a **starter `/docs/ai/` pack for Yore**. Frop these files straight into tje repo, then tweak them as the framework evolves.

Here are  **10 small files**, each short and structured. They’re Markdown (except the `.yml` repo map).

---

# `/docs/ai/00-yore-overview.md`

```markdown
# Yore Framework Overview

**Purpose:**  
Yore is a lightweight multi-domain, multi-tenant web framework. A single codebase can dynamically serve multiple websites, each with its own themes, modules, and domain-specific views.

**Core Mental Model:**
1. Request enters at `public/index.php`.
2. Bootstrap resolves environment and domain.
3. Domain → site config determines active theme + modules.
4. Router resolves route to a Module or controller method.
5. Module prepares data.
6. Blade (via jenssegers/blade) renders a template.

**Key Directories:**
- `/modules/` → Feature modules, each with `Module.php`.
- `/pages/` → Shared views.
- `/pages/_domains/` → Domain-specific overrides.
- `/templates/` → Layouts, partials, shared components.
- `/cli.php` → CLI entry point, accessed via `./yore`.
```

---

# `/docs/ai/01-architecture.md`

```markdown
# Yore Architecture

- **Bootstrap:** Initializes autoloading, config, DB, and environment.
- **Domains:** Each incoming request maps to a domain record → which site, theme, and config apply.
- **Routing:** Determines controller or Module method to handle request.
- **Modules:** Each self-contained in `/modules/<Name>/`. Must include a `Module.php` with `register()` and action methods.
- **Templates:** Blade views, organized under `/templates/`.
- **Pages:** Site views, overridden per domain if needed (`/pages/_domains/<domain>/`).
- **CLI:** The `yore` script dispatches tasks to `cli.php` for management (domains, modules, cache, migrations, etc.).
```

---

# `/docs/ai/02-conventions.md`

```markdown
# Yore Conventions

- **File Naming:**
  - Module entry: `/modules/<Name>/Module.php` defines `Modules\<Name>\Module`.
  - Views: lowercase + hyphens (`about-us.blade.php`).
- **Blade Usage:**
  - Layouts: `templates/layouts/`.
  - Partials: `templates/partials/`.
  - Components: `templates/components/`.
  - Always `@extends('templates.layouts.app')` for standard pages.
- **Domain Overrides:**
  - Place domain-specific views in `/pages/_domains/<domain>/`.
- **Coding:**
  - PSR-4 namespaces.
  - Keep module classes <300 lines; extract helpers if larger.
  - Config never hardcoded → always from env or domain record.
```

---

# `/docs/ai/03-cli.md`

````markdown
# Yore CLI Usage

**Dispatcher:**  
`./yore` → Bash wrapper that passes commands to `cli.php`.

**Examples:**
```bash
./yore cli module:list
./yore cli module:make Blog
./yore cli domain:add example.com
./yore cli cache:clear
./yore cli migrate
````

**Pattern:**

* `yore cli <command> [args...]`
* Commands should be idempotent.
* Always document new CLI verbs in this file.

````

---

# `/docs/ai/04-modules.md`

```markdown
# Yore Modules

**Structure:**
````

modules/
Users/
Module.php
migrations/
views/

```

**Required:**  
- `Module.php` defines `register()` and feature methods.
- Modules self-register routes and features.
- Views inside `modules/<Name>/views/`.

**Guidelines:**
- Keep modules isolated. No cross-module calls without contracts.
- Add migrations in `migrations/`.
- Provide CLI tasks if the module needs batch ops.
```

---

# `/docs/ai/05-routing.md`

````markdown
# Yore Routing

**Flow:**
1. Request arrives with domain.
2. Domain maps to site config.
3. Router resolves path → Module or controller.
4. Action returns array of data.
5. Blade renders view.

**Example:**
```php
// modules/Hello/Module.php
Router::get('/hello', [$this, 'show']);
````

**Rules:**

* Keep routes declared inside `register()`.
* Avoid hardcoded domains; domain handling is separate.

````

---

# `/docs/ai/06-views.md`

```markdown
# Yore Views

- **Engine:** Jenssegers/Blade
- **Layouts:** `/templates/layouts/`
- **Partials:** `/templates/partials/`
- **Components:** `/templates/components/`
- **Pages:** `/pages/` (global), `/pages/_domains/<domain>/` (overrides)

**Example Layout:**
```blade
<!doctype html>
<html>
  <head>
    <title>@yield('title', 'Yore')</title>
  </head>
  <body>
    <header>@include('templates.partials.nav')</header>
    <main>@yield('content')</main>
  </body>
</html>
````

````

---

# `/docs/ai/07-database.md`

```markdown
# Yore Database Conventions

- **Migrations:** Each module may have `/migrations/` with standard Laravel migration classes.
- **Eloquent:** Allowed, but keep logic in repositories/services, not controllers.
- **Raw SQL:** Use only when performance demands.
- **Naming:**
  - snake_case table names.
  - Foreign keys: `<singular>_id`.
- **CLI:** Run via `./yore cli migrate`.
````

---

# `/docs/ai/08-examples.md`

````markdown
# Yore Golden Examples

## Example A: Hello World Site
- Create `modules/Hello/Module.php`
```php
<?php
namespace Modules\Hello;

class Module {
  public function register(): void {
    Router::get('/hello', [$this, 'show']);
  }
  public function show() {
    return ['title' => 'Hello', 'message' => 'It works!'];
  }
}
````

* Add `/pages/home.blade.php`:

```blade
@extends('templates.layouts.app')
@section('title', $title ?? 'Home')
@section('content')
  <h1>{{ $message ?? 'Welcome' }}</h1>
@endsection
```

## Example B: CSV Import Flow

* Module `Import/Module.php` registers `/import`.
* Action: handles file upload, parses CSV.
* CLI: `./yore cli import:run file.csv`.
* Views: `/modules/Import/views/form.blade.php`.

````

---

# `/docs/ai/99-glossary.md`

```markdown
# Yore Glossary

- **Domain Theme:** Set of layouts, assets, styles bound to a domain.
- **Template:** Shared Blade layout/partials.
- **Module:** Self-contained feature (code + views + migrations).
- **Tenant:** A site served under a domain.
- **Runner:** CLI task executed via `yore`.
- **Dispatcher:** `yore` Bash script that calls `cli.php`.
- **Pages:** Standard site views.
- **Domain Overrides:** Domain-specific replacement views in `/pages/_domains/`.
````

---

# `/docs/ai/repo-map.yml`

```yaml
app_root: .
important_paths:
  bootstrap: bootstrap/
  public: public/
  config: config/
  storage: storage/
  routes: routes/
  modules: modules/
  pages: pages/
  templates: templates/
  pages_domains: pages/_domains/
  cli_entry: cli.php
  dispatcher: yore
views_blade:
  engine: jenssegers/blade
  layouts: templates/layouts/
  partials: templates/partials/
  components: templates/components/
yore_concepts:
  - multi-domain, multi-tenant from single codebase
  - domain -> site config -> route -> module/controller -> blade render
  - modules provide features, templates provide look/feel
```

---

✅ With these 10 files in `/docs/ai/`, you can point Roo Code (or any other agent) to load them as **context memory**.
That way, the AI always “knows Yore” before generating or editing code.

---



To be continued...

Need to also create an agent-seed.md (the AI prompt boilerplate you can feed Roo Code or ChatGPT every time) so it knows how to behave with Yore

Need to flesh out more advanced patterns (multi-tenant DB tricks, caching layers, etc.), and add them to the pack

