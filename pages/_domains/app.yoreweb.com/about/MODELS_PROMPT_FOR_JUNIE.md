# Prompt for Junie: Add domain-scoped Models and examples for `app.yoreweb.com`

This has been implemented.  See the module "Orm" and stuff

**Objective:**
Enable developers to place PHP model classes (extending our Yore ORM `Model`) in a domain-local `Models/` folder and import them in any custom controller via a predictable namespace. Implement this for the demo domain `app.yoreweb.com` with three example models and show usage in the demo `about/Controller.php`.

## 1) Namespace & folder convention (standard)

* **Folder:**
  `/pages/_domains/{domain}/Models/`
* **Namespace:**
  `Domain\<StudlyDomain>\Models`
* **StudlyDomain rule:**
  Take the exact domain string, remove dots `.` and hyphens `-`, capitalize each segment → `app.yoreweb.com` → `AppYorewebCom`; `store.example-site.org` → `StoreExampleSiteOrg`.
* **Example mapping:**
  `/pages/_domains/app.yoreweb.com/Models/User.php` → `Domain\AppYorewebCom\Models\User`

> Rationale: Controllers in Yore may remain un-namespaced (instantiated directly by the main controller), but Models benefit from namespacing for clarity and autoloading.

## 2) Runtime PSR-4 mapping (automatic per request)

Add a small bootstrap step (where the request domain is already resolved) to register a PSR-4 mapping for the current domain’s Models namespace to its `Models/` directory:

* Create a tiny utility (or place in an existing bootstrap area), e.g. `Yore\Domain\DomainAutoload::registerModels(string $domain, string $domainPath)`.
* Inside, compute `StudlyDomain` and register:

    * Namespace prefix: `Domain\{StudlyDomain}\Models\`
    * Path: `$domainPath . '/Models/'`
* Use Composer’s ClassLoader if available; otherwise, add a minimal `spl_autoload_register` that resolves the prefix to files.

**Acceptance:**
When a request hits `app.yoreweb.com`, `use Domain\AppYorewebCom\Models\User;` resolves automatically without manual `require`.

## 3) Example Models for `app.yoreweb.com`

Create these files:

`/pages/_domains/app.yoreweb.com/Models/User.php`

```php
<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['name','email','is_active','created_at','updated_at'];
}
```

`/pages/_domains/app.yoreweb.com/Models/Text.php`

```php
<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class Text extends Model
{
    protected static string $table = 'texts';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['slug','title','body','created_at','updated_at'];
}
```

`/pages/_domains/app.yoreweb.com/Models/SiteData.php`

```php
<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class SiteData extends Model
{
    protected static string $table = 'sitedata';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['k','v','updated_at'];

    // Convenience helpers
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('k', '=', $key)->first();
        return $row?->getAttribute('v') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $existing = static::where('k', '=', $key)->first();
        if ($existing instanceof self) {
            $existing->setAttribute('v', $value);
            $existing->save();
        } else {
            static::create(['k'=>$key, 'v'=>$value, 'updated_at'=>date('c')]);
        }
    }
}
```

## 4) Example usage in `about/Controller.php`

Open `/pages/_domains/app.yoreweb.com/about/Controller.php` and add simple examples that exercise CRUD, queries, pluck, and reading SiteData. Keep the controller un-namespaced but import models via `use`.

At the top of the file:

```php
<?php
declare(strict_types=1);

use Domain\AppYorewebCom\Models\User;
use Domain\AppYorewebCom\Models\Text;
use Domain\AppYorewebCom\Models\SiteData;

// ... existing controller code ...
```

Inside the controller action (where you render the /about page), add examples like:

```php
// 1) Basic query & pluck
$activeUsers = User::where('is_active', '=', 1)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

$userEmails = $activeUsers->pluck('email');

// 2) Fetch a Text page by slug
$aboutPage = Text::where('slug', '=', 'about')->first();
$aboutTitle = $aboutPage?->getAttribute('title') ?? 'About';
$aboutBody  = $aboutPage?->getAttribute('body')  ?? 'Welcome to our site.';

// 3) Site settings read/write
$currentTheme = SiteData::get('theme', 'default');
if (!$currentTheme) {
    SiteData::set('theme', 'default');
    $currentTheme = 'default';
}

// 4) Demonstrate create/update
if (!User::where('email','=','demo@example.com')->exists()) {
    $demo = User::create([
        'name'  => 'Demo User',
        'email' => 'demo@example.com',
        'is_active' => 1,
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ]);
    // quick update
    $demo->setAttribute('name', 'Demo User Jr.');
    $demo->save();
}

// 5) Pass data to the view (Blade or Fred as configured)
$viewData = [
    'title'       => $aboutTitle,
    'body'        => $aboutBody,
    'activeUsers' => $activeUsers->all(),
    'emails'      => $userEmails,
    'theme'       => $currentTheme,
];

// Render with existing view system
return $this->render('about', $viewData); // adapt to Yore’s render helper in your controller
```

> If the about controller returns JSON in API mode, you can instead do:
> `return $this->json($viewData);` (use the actual helper your base controller provides).

## 5) Optional seed SQL (only if handy)

If a quick seed helps the demo, add a tiny fixture loader (SQLite in-memory or MySQL) that creates the `users`, `texts`, and `sitedata` tables (using our `Schema`), then inserts a few rows. Keep it behind a dev-only flag or CLI command so production isn’t touched.

## 6) Acceptance criteria

* When hitting `https://app.yoreweb.com/about`, the controller successfully imports the models via `use Domain\AppYorewebCom\Models\...` with **no manual require** calls.
* Queries run using the domain models; the page renders the pulled data (or uses fallbacks if the rows don’t exist).
* The namespace mapping is **automatic per domain** via the runtime PSR-4 registration (no per-domain composer.json editing).
* Nothing changes for existing controllers; this is additive.

**Please implement now.** If any Yore bootstrap detail (e.g., where to hook the autoload registration) differs from the above, follow Yore’s conventions and place the mapping in the correct lifecycle hook. Keep the public convention intact:

```
/pages/_domains/{domain}/Models/*
namespace Domain\<StudlyDomain>\Models;
```
