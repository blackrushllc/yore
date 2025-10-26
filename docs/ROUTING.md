`# Routing

## Routable Models

Pattern:
/{site}/models/{Model}/{action}[/{arg1}/{arg2}/{arg3}]

Resolution:
- Domain namespace prefix: Domain\\{StudlyDomain}\\Models\\
- Domain folder: /pages/_domains/{domain}/Models/
- {StudlyDomain} is derived from the host (e.g., app.yoreweb.com => AppYorewebCom)
- {Model} is studly-cased from the URL segment (orders => Orders, user_profile => UserProfile)

Behavior:
- Prefer web_* for /.../models/... routes; api_* used if web_* missing.
- Method signature receives (Controller $controller, array $_REQUEST, ...$args).
- web_* should return body HTML; it's wrapped in the domain theme like any normal page.
- api_* returns raw output; arrays/objects are JSON-encoded.

Errors:
- 404 if the model class does not exist in the current domain.
- 404 if neither web_* nor api_* exists for the action.

Notes:
- This routing layer does not replace existing page or module routing.
- Autoload for Domain models is registered at runtime so classes under Domain\\*\\Models resolve to /pages/_domains/*/Models.
