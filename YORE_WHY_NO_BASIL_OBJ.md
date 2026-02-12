### Short answer
Because Yore is a cross‑cutting request pipeline (routing + env + rendering) rather than an object type, it needed tight, early access to the VM’s CGI/env caches, path resolution, file I/O, and the existing `RENDER$` engine. Implementing it as compiler/VM builtins kept the API simple (`YORE_*` functions), avoided new public VM hooks, and ensured predictable request handling and performance. It’s still fully opt‑in behind the `obj-yore` feature flag.

### Technical rationale
- VM/renderer integration
  - Yore must hand templates directly to `RENDER$` and rely on its include semantics and FRED directives. Those are implemented inside the VM. Keeping Yore close to the renderer avoids duplicating or exposing new rendering internals.
- Correct handling of CGI/env and POST body
  - The VM already parses and caches `QUERY_STRING`, `CONTENT_LENGTH`, headers, and form bodies once per request. A separate crate would either re‑parse stdin/env or require new public VM APIs. Builtins reuse the existing caches without expanding VM surface area.
- Bootstrapping and dynamic Basil loading
  - Future Yore features (custom controllers, Modeltrollers, domain modules) require loading Basil source at runtime and invoking functions. The VM already has `CLASS(...)`/inner‑VM execution paths. Doing that from an external crate would need new public hooks or a re‑entrant interpreter API.
- Simplicity of the BASIC API
  - Yore is naturally a set of top‑level functions (`YORE_INIT%`, `YORE_REQUEST`, …), not an object with methods. Mapping these as builtins (opcodes 251–256) is the most direct way the compiler/VM currently supports for global functions gated by a feature.
- Performance and fewer dependency edges
  - Domain detection, JSON parsing, and template load happen on every request. Keeping the hot path in the VM avoids extra trait/object dispatch layers and cross‑crate back‑and‑forth.

### Why not a `basil-objects-yore` crate (at least initially)?
- Most “feature objects” (CSV, ZIP, CURL, ORM, etc.) are true objects that fit the `basil-objects` registry model (NEW_OBJ → methods/props). Yore isn’t an object; it orchestrates the program entry flow. For now, our compiler doesn’t have a generic plug‑in registry for global functions outside builtins.
- A separate crate would force us to:
  - Expose new VM public APIs for request env access, stdin body reads, and renderer invocation, or
  - Reimplement duplicates of those pieces.
  Both options increase complexity for the first landing.

### What remains modular/opt‑in
- It’s gated by `--features obj-yore` in `basilc`, `compiler`, and `vm`. If you don’t enable it, none of the code or builtins are included.
- The implementation is intentionally narrow: domain/section routing, JSON page load, context building, and view resolution. Module hooks, controllers, and Modeltrollers can be layered in next without changing the API.

### Migration path if we prefer a separate crate later
- Thin builtins, external engine
  - Keep tiny `YORE_*` builtins in the VM, but delegate the actual work to a `basil-objects-yore` crate behind a trait the VM calls into. That preserves the simple BASIC API while moving logic out of `basilcore`.
- Host-global function registry
  - Add a generic “host function” registry (similar to object registry, but for global functions). Then `basil-objects-yore` could register `YORE_*` without new opcodes. This is a broader compiler feature and could benefit other host‑level utilities.
- Renderer and CGI helpers as stable VM APIs
  - If we want zero Yore code in the VM, we can formalize small public helpers (render, env/headers/POST access). Then `basil-objects-yore` can depend only on those.

### Bottom line
We put the first cut in `basilcore` to get a reliable, minimal, and fast end‑to‑end kernel with the current compiler/VM capabilities. It’s feature‑gated, doesn’t affect builds that don’t opt in, and we left clear paths to extract most logic into a `basil-objects-yore` crate once we finalize the small VM APIs needed to support it cleanly.