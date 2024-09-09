# Modules

## Module class naming convensions

Prefix: `yore_`

Example: `function yore_module_init($controller) { }`

Functions with this prefix are expected to be called by the controller to provide specific 
capabilities to the application such as augmenting the navigation bar.

Prefix: `fred_`

Example: `function fred_isadmin() { }`

Functions with this prefix provide extended embedded Ph@ (aka "phat") functions within view
templates.  Templates containing Ph@ notation are expected to be named as `my_template.html`
and contain a mix of Php, HTML and Ph@ directives and embedded functions. For the example above,
the Ph@ functions `@isadmin()` would evaluate to 1 or 0 based on the current user's admin status
and could be used for conditional output with a directive such as `@if(@isadmin())` and `@endif()`

Prefix: `api_`

Example: `function api_login($method) { }`

Functions with this prefix are called by routing API endpoints where the URL is similar to
`/api/users/login/` with the HTTP Request Method (i.e. "get" or "post", etc) passed as a 
parameter. The return value of the function is converted to JSON by the Controller.

_Note: In the future I plan to move API functions to a separate class file that is loaded on
demand_ by the API Controller but for now this is just part of the regular controller class


