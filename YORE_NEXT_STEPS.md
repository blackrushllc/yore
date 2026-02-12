### Overview
This update extends the initial `obj-yore` kernel with three major capabilities:

- Module hooks: call a Basil function declared in per-domain `_modules` to enrich page context.
- Custom controllers: dispatch to `web_*` and `api_*` functions in per-section `Controller.basil`.
- Modeltrollers: route `/section/models/Model/action[/arg1/arg2/arg3]` to `web_*` / `api_*` functions implemented in domain models.

Everything remains behind the `obj-yore` feature flag, requires no changes to the BASIC API, and preserves the existing page-JSON + view pipeline as a fallback when a controller/modeltroller is not present.

---

### What changes (at a glance)
- `YORE_REQUEST` now recognizes `/api/...` and Modeltroller paths; sets `is_api%`, `is_modeltroller%`, `model$`, `model_studly$`, and `action$` as appropriate.
- `YORE_BUILD_CONTEXT` now executes a page’s `module_hook` (if present), loading functions from domain `_modules` → `modules` → `Modules` and merging returned keys into the context.
- New internal helpers load Basil source files at runtime as `CLASS`-like instances and call methods.
- `YORE_HANDLE_REQUEST$` tries, in order:
  1) Modeltroller dispatch (if request matches the pattern),
  2) Section custom controller (`web_{pagekey}` or `api_{pagekey}`),
  3) Falls back to the existing page JSON + view pipeline.
- For API dispatch, if the handler returns an object/dict/list/number/bool/null, it is JSON-encoded into a string so that `YORE_HANDLE_REQUEST$` always returns a string body.

---

### Implementation details (Rust, `basilcore/vm/src/lib.rs`)
Below are focused code inserts that build on your current file. They can be added where indicated by comments or near adjacent helpers already present in your Yore section.

#### 1) Request recognition tweaks
Add API and Modeltroller awareness inside `fn yore_build_request(&mut self) -> Result<Value>` just after tokenizing `segs` and before computing final `section/pagekey/args`:

```rust
// Recognize /api/... prefix
let mut is_api = false;
if !segs.is_empty() && segs[0].eq_ignore_ascii_case("api") {
    is_api = true;
    segs.remove(0);
}

// Recognize modeltroller routing: /{section}/models/{Model}/{action}[/{arg1}/{arg2}/{arg3}]
let mut is_model = false;
let mut model_slug: Option<String> = None;
let mut action_slug: Option<String> = None;
if segs.len() >= 4 && segs[1].eq_ignore_ascii_case("models") {
    is_model = true;
    model_slug = Some(self.yore_slugify(segs[2]));
    action_slug = Some(self.yore_slugify(segs[3]));
}
```

Then compute `section/pagekey` with awareness of these flags:

```rust
let (section_raw, pagekey_raw, a1, a2, a3) = if is_model {
    // section is segs[0]; we keep pagekey as action for completeness
    let sec = segs[0];
    let arg1 = segs.get(4).copied().unwrap_or("");
    let arg2 = segs.get(5).copied().unwrap_or("");
    let arg3 = segs.get(6).copied().unwrap_or("");
    (sec, action_slug.as_deref(), arg1, arg2, arg3)
} else if segs.is_empty() {
    ("default", Some("home"), "", "", "")
} else if segs.len() == 1 {
    (segs[0], Some(if is_api { "index" } else { "home" }), "", "", "")
} else {
    let sec = segs[0];
    let page = segs[1];
    let arg1 = segs.get(2).copied().unwrap_or("");
    let arg2 = segs.get(3).copied().unwrap_or("");
    let arg3 = segs.get(4).copied().unwrap_or("");
    (sec, Some(page), arg1, arg2, arg3)
};
```

After filling the usual map fields, also set these extras:

```rust
map.insert("is_api%".to_string(), Value::Int(if is_api {1} else {0}));
map.insert("is_modeltroller%".to_string(), Value::Int(if is_model {1} else {0}));
if let Some(ms) = model_slug { map.insert("model$".to_string(), Value::Str(ms.clone())); map.insert("model_studly$".to_string(), Value::Str(self.yore_studly(&ms))); }
if let Some(act) = action_slug { map.insert("action$".to_string(), Value::Str(act)); }
```

Add the `yore_studly` helper next to `yore_slugify`:

```rust
#[cfg(feature = "obj-yore")]
fn yore_studly(&self, s: &str) -> String {
    let mut out = String::new();
    let mut cap = true;
    for ch in s.chars() {
        if ch == '_' || ch == '-' || ch == ' ' || ch == '.' { cap = true; continue; }
        if cap { out.extend(ch.to_uppercase()); cap = false; } else { out.extend(ch.to_lowercase()); }
    }
    out
}
```

#### 2) Module directory helpers
Add helpers near the other Yore helpers:

```rust
#[cfg(feature = "obj-yore")]
fn yore_modules_dir(&self, domain_dir: &Path) -> Option<PathBuf> {
    for nm in ["_modules", "modules", "Modules"] {
        let p = domain_dir.join(nm);
        if p.is_dir() { return Some(p); }
    }
    None
}

#[cfg(feature = "obj-yore")]
fn yore_models_dir(&self, domain_dir: &Path) -> Option<PathBuf> {
    for nm in ["_models", "Models"] {
        let p = domain_dir.join(nm);
        if p.is_dir() { return Some(p); }
    }
    None
}
```

#### 3) Runtime Basil loader and call helpers
Reuse the `CLASS(...)` logic from inside the VM to load a Basil file and make a callable object:

```rust
#[cfg(feature = "obj-yore")]
fn yore_load_class_obj_from_file(&mut self, file_path: &Path) -> Result<basil_bytecode::ObjectRef> {
    let fname = file_path.to_string_lossy().to_string();
    // Reuse the same loader NewClass uses
    let (prog, resolved_path) = self.load_class_program(&fname)?;
    let mut inner = VM::new(prog.clone());
    inner.set_script_path(resolved_path.clone());
    inner.run()?;
    let class_vals = inner.globals.clone();
    let inst = ClassInstance::new(prog.globals.clone(), class_vals);
    Ok(std::rc::Rc::new(std::cell::RefCell::new(inst)))
}

#[cfg(feature = "obj-yore")]
fn yore_object_has_method(&self, rc: &basil_bytecode::ObjectRef, name: &str) -> bool {
    let desc = rc.borrow().descriptor();
    desc.methods.iter().any(|m| m.name.eq_ignore_ascii_case(name))
}

#[cfg(feature = "obj-yore")]
fn yore_call_method(&mut self, rc: &basil_bytecode::ObjectRef, name: &str, args: &[Value]) -> Result<Value> {
    rc.borrow_mut().call(name, args)
}
```

#### 4) Module hook invocation from `YORE_BUILD_CONTEXT`
At the end of `fn yore_build_context(&self, req: &Value, page: &Value) -> Result<Value>` (after copying page fields) add:

```rust
// module_hook execution: module function returns a dict to merge
if let Some(Value::Str(hook)) = pm.get("module_hook$") {
    if let Some(extra) = self.yore_invoke_module_hook(hook, req)? {
        if let Value::Dict(extra_rc) = &extra {
            for (k, v) in extra_rc.borrow().iter() { ctx.insert(k.clone(), v.clone()); }
        }
        // Also expose under ctx key if view wants to inspect
        ctx.insert("module_data@".to_string(), extra);
    }
}
```

Then implement the helper used above:

```rust
#[cfg(feature = "obj-yore")]
fn yore_invoke_module_hook(&mut self, hook: &str, req: &Value) -> Result<Option<Value>> {
    let y = self.yore.as_ref().ok_or_else(|| BasilError("YORE: not initialized".into()))?;
    let mdir = match self.yore_modules_dir(&y.domain_dir) { Some(p)=>p, None=> return Ok(None) };
    let Ok(rd) = fs::read_dir(&mdir) else { return Ok(None) };
    for ent in rd.flatten() {
        let p = ent.path();
        if !p.is_file() { continue; }
        if let Some(ext) = p.extension() { if ext.to_string_lossy().to_ascii_lowercase() != "basil" { continue; } } else { continue; }
        // Load module file and look for hook method
        if let Ok(obj) = self.yore_load_class_obj_from_file(&p) {
            if self.yore_object_has_method(&obj, hook) {
                let v = self.yore_call_method(&obj, hook, std::slice::from_ref(req))?;
                return Ok(Some(v));
            }
        }
    }
    Ok(None)
}
```

Notes:
- Expected module hook signature: `FUNCTION HOOKNAME(req@)` → returns a `Dict` (merged into `ctx@`). Returning other types is allowed and will be placed in `ctx@` under `module_data@`.

#### 5) Custom controllers
Add helpers:

```rust
#[cfg(feature = "obj-yore")]
fn yore_controller_file(&self, domain_dir: &Path, section_dir: &str) -> Option<PathBuf> {
    let sec_dir = domain_dir.join(section_dir);
    for nm in ["Controller.basil", "controller.basil"] {
        let p = sec_dir.join(nm);
        if p.is_file() { return Some(p); }
    }
    None
}

#[cfg(feature = "obj-yore")]
fn yore_try_section_controller(&mut self, req: &Value) -> Result<Option<Value>> {
    let y = self.yore.as_ref().ok_or_else(|| BasilError("YORE: not initialized".into()))?;
    let reqm = match req { Value::Dict(rc)=>rc.borrow(), _=> return Ok(None) };
    let section = match reqm.get("section$") { Some(Value::Str(s))=>s.clone(), _=>"default".to_string() };
    let pagekey = match reqm.get("pagekey$") { Some(Value::Str(s))=>s.clone(), _=>"home".to_string() };
    let is_api = matches!(reqm.get("is_api%"), Some(Value::Int(i)) if *i != 0);
    let section_dir = self.yore_section_dirname(&y.domain_dir, &section);
    let Some(ctrl_path) = self.yore_controller_file(&y.domain_dir, &section_dir) else { return Ok(None) };
    let obj = self.yore_load_class_obj_from_file(&ctrl_path)?;
    // Prefer web_* for non-API; api_* for API
    let try_names = if is_api {
        vec![format!("api_{}", pagekey), format!("web_{}", pagekey)]
    } else {
        vec![format!("web_{}", pagekey), format!("api_{}", pagekey)]
    };
    // Optional arg1..arg3
    let a1 = reqm.get("arg1$").cloned().unwrap_or(Value::Str(String::new()));
    let a2 = reqm.get("arg2$").cloned().unwrap_or(Value::Str(String::new()));
    let a3 = reqm.get("arg3$").cloned().unwrap_or(Value::Str(String::new()));
    for name in try_names {
        if self.yore_object_has_method(&obj, &name) {
            // Call signature: FUNCTION web_name(req@, arg1$, arg2$, arg3$)
            let args = vec![req.clone(), a1.clone(), a2.clone(), a3.clone()];
            let v = self.yore_call_method(&obj, &name, &args)?;
            return Ok(Some(v));
        }
    }
    Ok(None)
}
```

#### 6) Modeltroller dispatch
Add helper:

```rust
#[cfg(feature = "obj-yore")]
fn yore_try_modeltroller(&mut self, req: &Value) -> Result<Option<Value>> {
    let y = self.yore.as_ref().ok_or_else(|| BasilError("YORE: not initialized".into()))?;
    let reqm = match req { Value::Dict(rc)=>rc.borrow(), _=> return Ok(None) };
    let is_model = matches!(reqm.get("is_modeltroller%"), Some(Value::Int(i)) if *i != 0);
    if !is_model { return Ok(None); }
    let model = match reqm.get("model_studly$") { Some(Value::Str(s))=>s.clone(), _=> return Ok(None) };
    let action = match reqm.get("action$") { Some(Value::Str(s))=>s.clone(), _=> "index".to_string() };
    let is_api = matches!(reqm.get("is_api%"), Some(Value::Int(i)) if *i != 0);
    let Some(mdir) = self.yore_models_dir(&y.domain_dir) else { return Ok(None) };
    // Try file names: Studly.basil, studly.basil
    let candidates = [format!("{}.basil", model), format!("{}.basil", model.to_ascii_lowercase())];
    let mut obj_opt = None;
    for nm in candidates {
        let p = mdir.join(&nm);
        if p.is_file() {
            obj_opt = Some(self.yore_load_class_obj_from_file(&p)?);
            break;
        }
    }
    let obj = match obj_opt { Some(o)=>o, None=> return Ok(None) };
    let try_names = if is_api {
        vec![format!("api_{}", action), format!("web_{}", action)]
    } else {
        vec![format!("web_{}", action), format!("api_{}", action)]
    };
    let a1 = reqm.get("arg1$").cloned().unwrap_or(Value::Str(String::new()));
    let a2 = reqm.get("arg2$").cloned().unwrap_or(Value::Str(String::new()));
    let a3 = reqm.get("arg3$").cloned().unwrap_or(Value::Str(String::new()));
    for name in try_names {
        if self.yore_object_has_method(&obj, &name) {
            let args = vec![req.clone(), a1.clone(), a2.clone(), a3.clone()];
            let v = self.yore_call_method(&obj, &name, &args)?;
            return Ok(Some(v));
        }
    }
    Ok(None)
}
```

#### 7) Value → JSON helper for API responses
Add near the JSON helpers:

```rust
#[cfg(feature = "obj-yore")]
fn yore_value_to_json(&self, v: &Value) -> sj::Value {
    match v {
        Value::Null => sj::Value::Null,
        Value::Bool(b) => sj::Value::Bool(*b),
        Value::Int(i) => sj::Value::Number((*i).into()),
        Value::Num(n) => sj::Value::Number(sj::Number::from_f64(*n).unwrap_or_else(|| sj::Number::from(0))),
        Value::Str(s) => sj::Value::String(s.clone()),
        Value::List(rc) => sj::Value::Array(rc.borrow().iter().map(|x| self.yore_value_to_json(x)).collect()),
        Value::Array(arr_rc) => {
            let data = arr_rc.data.borrow();
            sj::Value::Array(data.iter().map(|x| self.yore_value_to_json(x)).collect())
        }
        Value::Dict(rc) => {
            let mut map = serde_json::Map::new();
            for (k, v) in rc.borrow().iter() { map.insert(k.clone(), self.yore_value_to_json(v)); }
            sj::Value::Object(map)
        }
        Value::Object(_) => sj::Value::String("[object]".to_string()),
        Value::StrArray2D { .. } => sj::Value::String("[strarray2d]".to_string()),
        Value::Func(_) => sj::Value::String("[function]".to_string()),
    }
}
```

#### 8) Dispatch wiring in `YORE_HANDLE_REQUEST$`
Replace the current body of the `256` builtin with logic that tries model/controller before JSON pages:

```rust
#[cfg(feature = "obj-yore")]
256 => { // YORE_HANDLE_REQUEST$()
    if argc != 0 { return Err(BasilError("YORE_HANDLE_REQUEST$ expects 0 arguments".into())); }
    if self.yore.is_none() {
        let (dom, ddir) = self.yore_detect_domain_and_dir();
        if let Some(dir) = ddir { self.yore = Some(YoreCtx { domain: dom, domain_dir: dir, env: None, modules: None, db: None }); }
    }
    let req = self.yore_build_request()?;

    // 1) Modeltroller
    if let Some(v) = self.yore_try_modeltroller(&req)? {
        let body = match v {
            Value::Str(s) => s,
            other => sj::to_string(&self.yore_value_to_json(&other)).unwrap_or_else(|_| "{}".to_string()),
        };
        self.stack.push(Value::Str(body));
        break;
    }

    // 2) Section controller
    if let Some(v) = self.yore_try_section_controller(&req)? {
        let body = match v {
            Value::Str(s) => s,
            other => sj::to_string(&self.yore_value_to_json(&other)).unwrap_or_else(|_| "{}".to_string()),
        };
        self.stack.push(Value::Str(body));
        break;
    }

    // 3) Fallback: JSON page + view
    let page = self.yore_resolve_page(&req)?;
    let ctx = self.yore_build_context(&req, &page)?;
    let tpl = self.yore_render_page_template(&page, &ctx)?;
    let ctx_rc = match ctx { Value::Dict(rc)=>rc, _=> return Err(BasilError("YORE: internal error building context".into())) };
    let html = render::render_template(self, &tpl, Some(ctx_rc))?;
    self.stack.push(Value::Str(html));
}
```

Note: `break;` above means returning from the builtin handler block. If your match arm style doesn’t use `break`, simply `return` via stacking the return value and proceeding.

#### 9) Optional: page resolution fallback to `home.json`
Already present. No change required.

---

### BASIC usage examples

#### A) Module hook
File: `pages\_domains\_local\default\home.json`

```json
{
  "title": "Welcome",
  "view": "home",
  "module_hook": "SHOP_PAGE_DATA"
}
```

File: `pages\_domains\_local\_modules\Shop.basil`

```basic
FUNCTION SHOP_PAGE_DATA(req@)
  DIM m@
  LET m@ = DICT()
  LET m@["featured_products@"]= LIST()
  ' ... fill from DB via ctx db@ if desired ...
  RETURN m@
END FUNCTION
```

The returned keys are merged into `ctx@`, and the full value is also available as `module_data@`.

#### B) Section custom controller
File: `pages\_domains\_local\about\Controller.basil`

```basic
FUNCTION web_team(req@, arg1$, arg2$, arg3$)
  RETURN "<h1>About → Team</h1><p>arg1=" + arg1$ + "</p>"
END FUNCTION

FUNCTION api_status(req@)
  DIM o@
  LET o@ = DICT()
  LET o@["ok%"] = 1
  LET o@["ts$"] = NOW$()
  RETURN o@
END FUNCTION
```

- Visiting `/about/team` returns the HTML from `web_team()`.
- Visiting `/api/about/status` returns a JSON string like `{ "ok%": 1, "ts$": "..." }`.

If neither `web_*` nor `api_*` is present for a page, the engine falls back to JSON page + view.

#### C) Modeltroller
Files:
- `pages\_domains\_local\_models\Orders.basil`

```basic
FUNCTION web_index(req@, arg1$, arg2$, arg3$)
  RETURN "<h2>Orders index</h2>"
END FUNCTION

FUNCTION api_store(req@, arg1$, arg2$, arg3$)
  DIM res@
  LET res@ = DICT()
  LET res@["stored%"] = 1
  RETURN res@
END FUNCTION
```

- Visit `/sales/models/orders/index` → calls `Orders.basil` → `web_index`.
- Visit `/api/sales/models/orders/store` → calls `api_store` and returns JSON.

Notes:
- The router derives `model_studly$` from the slug via StudlyCase rules, and tries `Orders.basil` (and a lowercase variant).
- Function signatures are expected as `FUNCTION web_action(req@, arg1$, arg2$, arg3$)` or `api_action` with the same signature.

---

### Developer notes and rationale
- Controllers and Modeltrollers use the VM’s existing `CLASS(...)` infrastructure under the hood, so we don’t need new public VM APIs.
- Module hook scanning loads the first Basil module file that defines the hook function. You can later optimize this by caching descriptors or by supporting a `module::func` naming convention.
- `YORE_HANDLE_REQUEST$` now returns a string body in all cases. Callers should adjust `Content-Type` based on their chosen endpoints (HTML vs API JSON).
- All changes remain behind `--features obj-yore` and do not affect builds that don’t opt-in.

---

### Documentation updates (summary)
Augment `docs\FEATURES\obj-yore.md`:
- Add the new request flags (`is_api%`, `is_modeltroller%`, `model$`, `model_studly$`, `action$`).
- Explain controller precedence and API JSON auto-encoding behavior in `YORE_HANDLE_REQUEST$`.
- Show the examples above.

---

### Next steps (optional enhancements)
- Support dotted `module_hook` names like `Shop::PageData` to target a specific module file.
- Theme/layout wrapper in `YORE_RENDER_PAGE$` based on `theme$`.
- Controller theming wrapper: let `web_*` HTML be inserted into a theme layout with page metadata.
- Better error reporting for missing controllers/models when `is_debug%` is set.

If you’d like, I can prepare exact patches for the code locations above or apply them in-line in a follow-up.