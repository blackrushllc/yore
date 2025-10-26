
---

# Prompt for Junie — Yore “Routable Models” + Model CRUD Helpers + DataTables Index

**You’re inside the Yore project in PhpStorm.** Use our AI onboarding docs as ground truth. Implement the following changes while keeping the codebase idiomatic and minimal.

Incidentally, I am coining the phrase Modeltroller. That's what we've created here. These are Modeltrollers. 

Just a nickname. We might use it in the future.

## 0) Scope & Constraints

* **No soft deletes** in this pass (we might add later).
* Keep public APIs small and obvious. Favor aliases for familiar actions (`store()` ⇒ `create()`).
* Models are **domain-scoped** in `/pages/_domains/{domain}/Models/` with namespace `Domain\<StudlyDomain>\Models\*`.
* Add a **Model routing layer** so `/{site}/models/{Model}/{action}` calls a Model’s `web_*` or `api_*` method:

    * `web_*` → render into theme stack (HTML path)
    * `api_*` → return raw output as-is (API path)
* Add **DataTables index support** that can read **raw `$_REQUEST`** with no massaging.
* Keep behavior compatible with Yore conventions: controller routing, themes, Modules, Fred/Blade, etc.

---

## 1) Files to Create / Modify

* `modules/Orm/src/Core/Model.php` (or the current path for `Modules\Orm\Core\Model`)
* `app/Controller.php` (core router) — add “Routable Models” resolution
* (If needed) small helpers under `app/Domain/` for namespace mapping (reuse existing domain autoload if already present)
* `/pages/_domains/app.yoreweb.com/Models/Orders.php` (demo model)
* `/pages/_domains/app.yoreweb.com/customers/views/` (optional demo view if you want to show both HTML and API)
* `/examples/routable-models.http` (optional: REST client examples)
* `/docs/MODELS.md` and `/docs/ROUTING.md` — append a short section on Routable Models (keep concise)
* Tests (lightweight smoke): `tests/Orm/ModelIndexTest.php`, `tests/Routing/ModelRoutingTest.php` (SQLite-backed)

Use strict types and our usual PSR-4.

---

## 2) Extend `Modules\Orm\Core\Model`: CRUD + Lookup Sugar + Index

In `Modules\Orm\Core\Model` (base Model), add the following **public API**:

### 2.1 CRUD aliases & helpers (static)

```php
// Alias: exactly like create()
public static function store(array $attrs): static;

// Find or create variants
public static function firstOrNew(array $match, array $values = []): static;      // not persisted
public static function firstOrCreate(array $match, array $values = []): static;   // persisted
public static function updateOrCreate(array $match, array $values): static;       // find+update OR create

// Bulk update based on where conditions
public static function updateWhere(array $values, array $where): int;

// Bulk insert (multi-row)
public static function insertMany(array $rows): int;

// (Optional in this pass; keep minimal but functional)
// Portable upsert skeleton; if complexity grows, stub for next prompt
public static function upsert(array $rows, array $uniqueBy, array $updateCols): int;

// Utility sugar
public static function findBy(string $column, mixed $value): ?static;
public static function whereIn(string $column, array $values): \Yore\ORM\QueryBuilder;

// Meta
public static function lastInsertId(): ?string;   // via DB::connection(...)->lastInsertId()
```

### 2.2 Instance helpers

```php
// Safer name than colliding with QueryBuilder::update()
public function updateAttributes(array $values): void;  // fill + save (dirty-only if cheap to implement)
public function refresh(): void;                        // reload from DB by PK
```

> **Implementation notes**

* `store()` → one-liner alias of `create()`.
* `firstOrNew()` → `static::query()->where(...)` with ANDed equals for `$match`; hydrate or `new static($match + $values, false)`.
* `firstOrCreate()` → as above, then `save()` if new.
* `updateOrCreate()` → find by `$match`; if found `updateAttributes($values)` else `create($match + $values)`.
* `updateWhere()` → build QB from `static::query()`; for each `$where[k]=v`, add `where(k,'=',v)`, then `update($values)`.
* `insertMany()` → fast path multi-row insert; use driver->statement with bound params, or loop `insert()` if that’s simpler for now.
* `upsert()` → **minimal**: implement MySQL “ON DUPLICATE KEY UPDATE” and SQLite “ON CONFLICT (…) DO UPDATE SET …”. If too long, leave a TODO and return 0; we’ll do it in the follow-up prompt.
* `findBy()` → simple where + first.
* `whereIn()` → pass to QB `where($col, 'IN', $values)`.
* `lastInsertId()` → `return DB::connection(static::$connection)->lastInsertId();`
* `updateAttributes()` → `fill($values); save();`
* `refresh()` → reload by PK using `find($this->getKey())`, then swap `$attributes` and `$exists`.

### 2.3 DataTables index (server-side) + direct `$_REQUEST` support

Add **both** methods:

```php
/**
 * DataTables-ready index with flexible input.
 * Accepts either DataTables params or a simple search map (e.g. ['find'=>'smith'] or ['username'=>'erik']).
 *
 * @param array $params DataTables or simple map (key=>value); if empty, treat as no filters.
 * @param array $options [
 *   'columns'    => ['id','username','email','zipcode','created_at'], // whitelist
 *   'searchable' => ['username','email','zipcode'],                    // whitelist
 *   'orderable'  => ['id','username','email','created_at'],            // whitelist
 *   'as'         => 'array'|'models'  // default 'array'
 *   'maxLength'  => 1000              // cap for DT length
 * ]
 * @return array { draw, recordsTotal, recordsFiltered, data }
 */
public static function index(array $params, array $options = []): array;

/**
 * Convenience wrapper that consumes $_REQUEST directly.
 * No massaging required; validates via whitelists in $options.
 */
public static function indexFromRequest(?array $options = null): array;
```

**Behavior requirements (keep minimal & safe):**

1. **Total rows**: `recordsTotal = static::query()->count()`.
2. **Build `$qb`** from `static::query()`:

    * If `$params` is a **simple map** (no `draw` key):

        * For each scalar `k=>v`, if `k` ∈ `searchable` or `columns`, add `where(k,'=',v)`.
        * If key `find` exists, build grouped `OR` over `searchable` with `LIKE ?` and `%term%`.
    * If **DataTables** input:

        * Global search: `search[value]` → grouped `OR` over `searchable`.
        * Per-column search: `columns[i][search][value]` (only for whitelisted columns).
        * Ordering: `order[*]` using `orderable` whitelist; ignore unknowns.
        * Pagination: `start`/`length` with `length` ≤ `maxLength` (default 1000).
3. **Filtered count**: `recordsFiltered = (clone $qb)->count()`.
4. **Fetch** page rows:

    * `'as' => 'array'`: return assoc arrays (fast path; use `select('*')->get()->all()`).
    * `'as' => 'models'`: hydrate to models then `toArray()` each.
5. **Return** `['draw'=> (int)$draw, 'recordsTotal'=>…, 'recordsFiltered'=>…, 'data'=>…]`.

**Security**: **Only** reference columns present in `columns/searchable/orderable`. Ignore others silently.

---

## 3) “Routable Models” in `app/Controller.php`

Add routing so that **page URLs** can call a Model method directly:

**Pattern:**
`/{site}/models/{ModelName}/{action}[/{arg1}/{arg2}/{arg3}]`

* `site` is the first slug (like existing pages).
* The literal segment `models` indicates Model routing.
* `ModelName` is the **class name** under the domain Models namespace:

    * Namespace: `Domain\<StudlyDomain>\Models\{StudlyModelName}`
    * Resolve `{StudlyModelName}` by sanitizing input: letters/digits/underscore only; convert kebab/pass-through to Studly if needed.
* `action` maps to a **method prefix** on the Model:

    * If the request is **API mode** (`/api/...` or if you prefer to keep it under pages only, *don’t* use `/api` prefix here)—we’ll keep it simple: we’re **not** using `/api` prefix for this feature. We’re using `/…/models/…`.
    * If the method exists as `api_{action}`, treat as **API**.
    * Else if the method exists as `web_{action}`, treat as **WEB**.
    * Else 404.

**Invocation rules:**

* Instantiate the Model (no args) → `$m = new {ModelClass}();`
* Build arguments:

    * Always pass **Controller** and **`$_REQUEST`** first (in that order) so handlers can read context:

        * `$args = [$this /*controller*/, $_REQUEST];`
    * Then pass any remaining URL args: `$args[] = $arg1; $args[] = $arg2; …` (only include non-null).
* **API method** (`api_*`) return value is **output directly** to the client (no theming). If it’s an array, JSON-encode; if it’s a scalar/string, echo as-is.
* **WEB method** (`web_*`) return value is treated as **body HTML**; pipe through the normal theming stack (header/navbar/body/footer) and Fred/Blade post-processing as the existing controller does for page controllers.
* If both `api_*` and `web_*` exist for same action, prefer `api_*` **only when** the URL is explicitly emitted as **API route** in future (not now). For this pass, just pick whichever exists; if both exist, prefer `web_*` for `/…/models/…` routes (document this in a code comment).

**Priority & interference:**

* This **does not replace** existing page or module routing. Insert it **after** you parse the site slug and **before** giving up to 404 for that site path. It should only trigger when the second slug equals `"models"`.

**Errors:**

* If `{ModelClass}` does not exist or cannot be autoloaded, 404.
* If target method is missing, 404.
* If method throws, bubble up via the usual error handling (and show diagnostics in debug).

**Autoloading:**

* Reuse the domain autoload mapping you wrote for domain-scoped Models (PSR-4 `Domain\<StudlyDomain>\Models\` → `pages/_domains/<domain>/Models/`). If not registered at this point in the lifecycle, register it earlier in the request bootstrap.

---

## 4) Demo: `app.yoreweb.com`

### 4.1 Create demo model: `/pages/_domains/app.yoreweb.com/Models/Orders.php`

```php
<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class Orders extends Model
{
    protected static string $table = 'orders';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['customer_id','number','status','total','created_at','updated_at'];

    // API endpoint to insert a row (example)
    public function api_store($controller, array $request, ...$args): array
    {
        // naive validation omitted for brevity
        $row = [
            'customer_id' => $request['customer_id'] ?? null,
            'number'      => $request['number']      ?? null,
            'status'      => $request['status']      ?? 'new',
            'total'       => $request['total']       ?? 0,
            'created_at'  => date('c'),
            'updated_at'  => date('c'),
        ];
        $order = static::store($row);
        return ['ok'=>true, 'id'=>$order->getKey()];
    }

    // Web endpoint to list orders with DataTables (uses raw $_REQUEST via indexFromRequest)
    public function web_index($controller, array $request, ...$args): string
    {
        $payload = static::indexFromRequest([
            'columns'    => ['id','customer_id','number','status','total','created_at'],
            'searchable' => ['number','status'],
            'orderable'  => ['id','number','status','created_at'],
            'as'         => 'array',
            'maxLength'  => 1000,
        ]);

        // Extremely simple HTML render; in real pages you’d use a view file.
        $rows = $payload['data'];
        $lis  = array_map(fn($r) => "<li>#{$r['id']} {$r['number']} {$r['status']} {$r['total']}</li>", $rows);
        return "<div class='container'><h1>Orders</h1><ul>".implode('', $lis)."</ul></div>";
    }
}
```

### 4.2 Example routes

* **API create:**
  `/customers/models/orders/store?customer_id=1&number=INV-123&total=9.99`
  → calls `Orders::api_store(...)` and returns JSON.

* **WEB list (DataTables-style or simple search):**
  `/customers/models/orders/index?search[value]=INV`
  → calls `Orders::web_index(...)`, returns HTML inside theme.

(Replace `customers` with your actual first-slug/site folder present for `app.yoreweb.com`.)

---

## 5) Controller plumbing changes (sketch)

In `app/Controller.php`, after you resolve `{domain}` and `{site}` and before 404:

```php
if ($this->name === 'models' && isset($this->arg1, $this->arg2)) {
    $modelSlug  = $this->arg1;   // e.g. "orders"
    $actionSlug = $this->arg2;   // e.g. "store" or "index"
    $args       = array_values(array_filter([$this->arg3 ?? null, $this->arg4 ?? null, $this->arg5 ?? null], fn($v)=>$v!==null));

    $studlyDomain = $this->studlyDomain(); // you likely already have this
    $studlyModel  = $this->studly($modelSlug); // write a small helper if needed

    $fqcn = "Domain\\{$studlyDomain}\\Models\\{$studlyModel}";
    if (!class_exists($fqcn)) return $this->abort404("Model {$fqcn} not found.");

    $instance = new $fqcn();

    // prefer WEB for /.../models/... ; API if only api_* exists
    $webMethod = "web_{$actionSlug}";
    $apiMethod = "api_{$actionSlug}";

    if (method_exists($instance, $webMethod)) {
        $html = $instance->{$webMethod}($this, $_REQUEST, ...$args);
        // Use your existing theming/render pipeline; treat $html as body content
        return $this->renderHtmlBody($html);
    } elseif (method_exists($instance, $apiMethod)) {
        $out = $instance->{$apiMethod}($this, $_REQUEST, ...$args);
        return $this->emitRaw($out); // array→JSON, string→echo, etc.
    }

    return $this->abort404("No routable method on {$fqcn} for action {$actionSlug}.");
}
```

Provide small helpers:

* `studly(string $slug): string` (e.g., `orders` → `Orders`, `user_profile` → `UserProfile`)
* `renderHtmlBody(string $body): mixed` — pipe through theme assemble like page controllers do.
* `emitRaw(mixed $out): mixed` — if array/object ⇒ `application/json`, else echo as-is.

---

## 6) Docs update (brief)

Append short sections:

* **`/docs/ROUTING.md`**:
  Add “Routable Models” subsection with the URL pattern, resolution order hook, and WEB vs API behavior.

* **`/docs/MODELS.md`**:
  Add “Routable methods” (`web_*` and `api_*`) and show example usage & signature (first args: Controller, `$_REQUEST`, then extra slugs). Document `index()` and `indexFromRequest()` with whitelist options and example DataTables request.

Keep it short and exact.

---

## 7) Tests / Smoke

* `tests/Orm/ModelIndexTest.php`:

    * Use SQLite in-memory; create `orders` table; insert 3 rows.
    * `index(['find'=>'INV-'])` returns 2 matches.
    * DataTables payload fields exist and numeric fields are ints.

* `tests/Routing/ModelRoutingTest.php`:

    * Simulate request to `/customers/models/orders/store?...` and assert JSON `ok:true`.
    * Simulate `/customers/models/orders/index?search[value]=INV` and assert body contains list item.

(Use the repo’s existing testing approach; keep them small to avoid adding a whole framework.)

---

## 8) Acceptance Criteria

* `Modules\Orm\Core\Model` exposes the new methods exactly as specified and they operate on both **MySQL** (PDO) and **SQLite**.
* `index()` and `indexFromRequest()` produce valid DataTables payloads and support simple map searches.
* `app/Controller.php` can route `/{site}/models/{Model}/{action}` to Model `web_*` or `api_*` methods:

    * WEB returns themed HTML;
    * API returns raw result (arrays as JSON).
* Demo `Orders` model works on `app.yoreweb.com`:

    * `/customers/models/orders/store?...` inserts and returns `{"ok":true,"id":...}`
    * `/customers/models/orders/index?...` renders HTML list (or a tiny table).
* Docs updated with concise, correct info.
* No regressions to existing Controllers/Modules routing.

---

## 9) Future (NOT in this pass; prepare follow-up prompt)

* Add `casts`, `timestamps`, `events` (creating/created/etc.), **true** portable `upsert()` in drivers, `insertOrIgnore`, `chunk()` and `cursor()`, and (later) **soft deletes** with a trait.

