### Goal
Add “Include Files” to the BASIC/Basil language, front end, the interpreter (basilc), and the bytecode compiler (bcc) so that source from another file can be virtually inserted at the include site. Phase 1 focuses on a compile-time directive preprocessor with include-once semantics. Optional Phase 2 explores a runtime include.

---

### Summary of the proposal
- Introduce a simple preprocessor layer that runs before tokenization/parsing.
- Syntactic form: `#include` directive (with include-once semantics), plus optional `#define` and `#if/#else/#endif` for conditional inclusion.
- Resolution model supports:
  - Local/project-relative includes: `#include "path/to/file.basil"` or `#include path/to/file.basil` (quotes optional for simple paths)
  - Embedded library includes shipped with basilc (angle-brackets): `#include <lib/eliza.basil>`
  - Search paths: current file directory, project root, `-I` CLI paths, environment `BASIL_PATH`, and embedded resources.
- Include-once via canonicalization + a visited set (cycles detected and reported nicely).
- Spans preserved across included files via a SourceMap so diagnostics still point to the correct file/line.
- Phase 1: Directives only. Phase 2: Optional runtime include for the interpreter with strict constraints; compiler support possible only if static and resolvable.

---

### Why a preprocessor (and not just lexer support)?
- Directives operate at the source level (lines, files), before tokenization, so a small preprocessor keeps the main lexer/parser unchanged and simpler.
- It also lets us add `#define` and `#if` consistently without complicating the grammar of BASIC itself.
- We can preserve precise file/line mappings with a SourceMap independent of the AST.

---

### Directive syntax (Phase 1)
- Recognition rule: A directive is recognized only when `#` is the first non‑whitespace character on a line. It is not recognized inside strings.
- Comments and blank lines are unaffected. Existing BASIC comments (`REM`, `//`, `'`, etc. as applicable) continue to work.

Supported directives:
- `#include` forms:
  - `#include "relative/or/absolute/path.basil"`
  - `#include <embedded/path.basil>`  (searches embedded table provided by basilc)
  - `#include path/without/spaces.basil` (bare path; optional convenience, but quotes recommended)
- `#define` and `#undef` for simple macro symbols or numeric/string values:
  - `#define NAME` (boolean-like definedness)
  - `#define NAME 123`
  - `#define NAME "text"`
  - `#undef NAME`
- `#if`, `#elif`, `#else`, `#endif` for conditional compilation around directives and source:
  - Expression grammar: literals (ints, strings), identifiers (expand to value or 1/0 if `defined(NAME)`), unary `!`, binary `== != < <= > >=`, boolean `&& ||`, parentheses `(...)`, function `defined(NAME)`.

Built-in predefined names:
- `__version__` (int or semver string; recommend numeric major or full semver string with string comparison)
- `__file__`, `__line__`
- `__os__` (e.g., "windows", "linux", "macos")
- `__engine__` ("basilc" for interpreter, "bcc" for compiler)
- `__debug__` (1/0 depending on build flags), optional

Examples:
```
#include examples/my_include.basic

#define FEATURE_X 1
#if __version__ < 2
    #include "version_1_stuff.inc"
#else
    #include "version_2_stuff.inc"
#endif
```

---

### Include-once semantics and cycle detection
- Strict include-once: the same resolved file (or embedded asset) is included only once per compilation unit, silently ignored on subsequent requests.
- Canonicalization:
  - For filesystem paths: resolve to absolute canonical path (normalize case per OS rules and resolve symlinks when available) and use that as the unique key.
  - For embedded resources: treat as `embedded:/logical/path` as a unique key.
- Cycle detection: maintain an include stack; if a request would re-enter a file already on the stack, emit a clear error with a stack trace of include sites.

Diagnostic example on cycle:
```
error: include cycle detected
  at app.basil:3:1 → includes/a.basil:10:1 → includes/b.basil:7:1 → includes/a.basil
```

---

### Search paths and resolution order
Forms and order:
1) Quoted: `#include "foo/bar.basil"`
- Resolution order:
  - Directory of the current file
  - Project root (if known, e.g., current working directory when invoking basilc/bcc)
  - CLI-provided include paths: `-I path1 -I path2`
  - Environment variable `BASIL_PATH` (semicolon-separated on Windows, colon on Unix)
  - Embedded includes (last resort)

2) Angle-brackets: `#include <lib/eliza.basil>`
- Resolution order:
  - Embedded includes (first)
  - CLI `-I` paths
  - Project root

3) Bare path without quotes: `#include path/with/no/spaces.basil`
- Treat as same as quoted form for resolution. If a space appears, require quotes and error if omitted.

Notes for Basil:
- You already ship embedded sample/library content under `basilc/includes` with a build step that generates an embedded table of `include_bytes!` (generated into `embedded_includes.rs`). Expose these to the preprocessor as an embedded provider so users can write `#include <examples/zip_demo.basil>` etc.

---

### Architecture: Preprocessor and SourceMap
Components:
- SourceManager
  - Owns all loaded sources (root and included) and assigns them file IDs.
  - Knows mapping of file ID → path (or `embedded:/...`) and content bytes.
  - Provides `resolve_include(request, from_file_id)` using the search model above.
  - Tracks include graph: for dependency reporting and rebuild triggers.
- Preprocessor
  - Consumes raw source lines, recognizes directives, and produces a flattened stream of bytes while preserving a mapping from output offsets to original (file, line) pairs.
  - Maintains a visited set keyed by canonical path or embedded key for include-once.
  - Evaluates `#if` expressions with macro table and built-ins.
- SourceMap
  - Records inclusive ranges mapping flattened output → original files and line ranges.
  - Exposed to lexer/parser so tokens carry precise spans; diagnostics can render full paths and include stacks.

Integration points:
- basilcore front end: place this as a new crate/module, e.g., `basilcore/preprocessor` or `basilcore/frontend/src/preprocessor.rs`.
- Provide a single entry point used by both basilc (interpreter) and bcc (compiler):
  - `PreprocessResult { text: String, source_map: SourceMap, dependencies: Vec<CanonicalKey> }`
  - Both consumers then hand `text` to the existing lexer/parser.

---

### bcc integration (compiler)
- The compiler should call the shared preprocessor first. That gives a flattened unit and a full dependency list.
- The dependency list can drive:
  - File change invalidation in incremental builds / watch mode.
  - Better error messages in IDEs.
- If a runtime include is later added, bcc can either:
  - Require the include argument to be a constant string at compile time (then treat it as a normal `#include` under the hood), or
  - Reject dynamic includes with a clear error: “runtime include unsupported by bcc; use `#include` or a constant string.”

---

### basilc integration (interpreter)
- The interpreter also calls the shared preprocessor prior to execution.
- Embedded includes: wire the existing embedded table (generated by `basilc/build.rs`) through an `IncludeProvider` implemented by basilc and passed into the preprocessor. This lets `#include <...>` work out of the box without touching the filesystem.
- `-I` flags: extend basilc CLI to accept multiple `-I` include dirs (mirrors compilers). Also support `BASIL_PATH`.

---

### Error handling and diagnostics
- File-not-found:
```
error: include not found: "utils/format.basil"
  searched in:
    - C:\proj\src
    - C:\proj
    - -I C:\basil\lib
    - BASIL_PATH entries
    - embedded library
  included from: app.basil:12:1
```
- Cycle detection as shown above.
- Conditional expression errors (unknown identifier, type mismatch): report with caret, show the problematic expression and allowed operators.
- Hard limits to prevent abuse:
  - Max include depth (e.g., 64)
  - Max total expanded size (configurable; default sufficient for typical projects)
  - Reasonable line-length limits to avoid pathological memory use

---

### Runtime include (Phase 2, optional)
Design choices and constraints:
- Syntax: a function form for the interpreter only:
  - `INCLUDE("file.basil")` or `include("file.basil")` (case-insensitive per language rules). Can be called conditionally.
- Semantics:
  - Loads, preprocesses, and executes the included file in the current module/global scope at the call point.
  - Include-once still enforced globally for safety and idempotency.
- Security and determinism:
  - Disabled by default in sandboxed or hosted environments.
  - Can be gated behind an interpreter flag `--allow-runtime-include`.
- bcc stance:
  - Either forbid non-constant runtime includes, or allow only constant-literal arguments that can be resolved at compile-time and thus flattened into the program image.

Recommendation: Ship Phase 1 first; revisit runtime include based on user demand.

---

### Cross-platform path handling
- Use UTF‑8 for source; normalize CRLF/CR line endings.
- On Windows, accept backslashes in user input but normalize internally to forward slashes for logical keys. Canonicalization must be case-insensitive on Windows.
- Resolve symlinks where the OS allows; otherwise normalize path segments.

---

### CLI additions
- basilc and bcc:
  - `-I, --include-path <dir>` (repeatable)
  - `--no-embedded-includes` to disable searching embedded library
  - `--D NAME[=VALUE]` to predefine macros from the command line (useful with `#if`)
  - `--allow-runtime-include` (interpreter only, Phase 2)

---

### Testing strategy
- Unit tests (preprocessor):
  - Single include, nested includes, deep chains (with depth limit), duplicate includes ignored, include cycles error.
  - Path resolution precedence for quoted vs angle-bracket includes.
  - Conditional blocks: truthy/falsy, defined(), numeric and string comparisons, nested `#if`/`#else`/`#elif`.
  - Span mapping: ensure diagnostics from the parser point to the correct included file/line.
  - Embedded include lookup (with a small fake table) and dedup across FS and embedded.
- Integration tests:
  - End‑to‑end basilc run on examples that use includes.
  - bcc compilation with includes and conditional branches.
- Performance/regression:
  - Large include trees, ensure reasonable memory/time.

---

### Documentation updates
- Add a new page: “Preprocessor Directives” covering `#include`, `#define`, `#if/#elif/#else/#endif` with examples.
- Update `KEYWORDS.md` to list directives (noting they are preprocessor constructs, not runtime keywords).
- Expand guides to show modular project layout with `includes/` and `-I` usage.

---

### Phased rollout plan
1) Phase 1 — Preprocessor with `#include` and include-once
- Implement SourceManager, Preprocessor, SourceMap.
- Wire into basilcore front end, then basilc and bcc.
- Add CLI `-I`, `--D`, and embedded include provider integration.
- Ship docs and tests.

2) Phase 1.5 — Conditionals and defines
- Add `#define/#undef` and `#if/#elif/#else/#endif` expression evaluator.
- Add built-in macros and CLI `--D` support.

3) Phase 2 — Optional runtime `include()` in interpreter
- Gate behind a flag; include-once enforced.
- For bcc: either require constant literals or disallow.

---

### Implementation sketch (Rust)
- New trait to abstract where includes come from:
```
pub trait IncludeProvider {
    fn try_read(&self, logical: &str) -> Option<Vec<u8>>; // for embedded
}
```
- The preprocessor receives:
```
pub struct PreprocessOptions<'a> {
    pub cwd: PathBuf,
    pub include_paths: Vec<PathBuf>,
    pub env_paths: Vec<PathBuf>, // from BASIL_PATH
    pub embedded: Option<&'a dyn IncludeProvider>,
    pub defines: HashMap<String, MacroValue>,
}
```
- Output:
```
pub struct PreprocessResult {
    pub text: String,
    pub source_map: SourceMap,
    pub dependencies: Vec<IncludeKey>, // canonicalized
}
```
- basilc wires its generated `EMBEDDED_FILES` (from `basilc/build.rs`) into an `IncludeProvider` so `#include <...>` works without disk.

---

### Opinions on the syntax you proposed
- `#include examples/my_include.basic` is fine; I recommend also supporting quoted and angle-bracket forms for robustness and to differentiate local vs embedded/library includes.
- Include-once by default is the right call; no need for options.
- The `#if`/`#else` blocks are excellent to add now (simple evaluator) so users can do version/engine/os‑dependent includes — they are easy to implement and very useful.
- Runtime `include("...")` can be powerful but should be strictly optional and interpreter-only at first to avoid complicating the compiler/linker story.

---

### Acceptance criteria
- Users can modularize code with `#include` and not worry about duplicates.
- Good errors with include stacks; cycles detected.
- Both basilc and bcc accept the same source files and respect the same include resolution.
- Embedded libraries exposed via angle-bracket include.
- Conditional directives available with a small, well-documented expression language.

If you want, I can draft the concrete parser and Rust scaffolding for the preprocessor (trait signatures and a minimal working implementation) next.