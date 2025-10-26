<!-- Home page, not logged in -->


    <style>
        :root{
            --bg: #0e1116;
            --panel: #151922;
            --ink: #e9eef7;
            --muted: #9aa6b2;
            --accent: #7dd3fc;      /* sky-ish */
            --accent-2: #a78bfa;    /* violet-ish */
            --ok: #34d399;
            --warn: #f59e0b;
            --err: #ef4444;
            --ring: rgba(125,211,252,.45);
            --shadow: 0 10px 30px rgba(0,0,0,.25);
            --radius: 16px;
        }
        @media (prefers-color-scheme: light){
            :root{ --bg:#f7fafc; --panel:#ffffff; --ink:#0b1220; --muted:#445063; --shadow:0 8px 20px rgba(0,0,0,.08); }
        }

        body, .basil-page{ background:var(--bg); color:var(--ink); padding-bottom:80px; }
        .wrap{ max-width:1100px; margin:0 auto; padding:24px; }
        .hero{ position:relative; border-radius: var(--radius); padding:48px 28px; background:
                radial-gradient(1000px 400px at 10% -10%, rgba(125,211,252,.18), transparent 60%),
                radial-gradient(900px 400px at 90% -20%, rgba(167,139,250,.15), transparent 60%),
                var(--panel);
            box-shadow: var(--shadow);
            overflow:hidden;
        }
        .hero h1{ font-size: clamp(32px, 5vw, 56px); line-height:1.05; margin:0 0 10px; letter-spacing:-.02em; }
        .tag{ display:inline-block; font-size:14px; color:var(--muted); border:1px solid rgba(255,255,255,.08); padding:6px 10px; border-radius:999px; margin-bottom:16px; background:rgba(255,255,255,.03) }
        .hero p{ color:var(--muted); font-size:18px; max-width:70ch }
        .cta-row{ display:flex; gap:12px; flex-wrap:wrap; margin-top:20px }
        .btn{ appearance:none; border:0; cursor:pointer; border-radius:12px; padding:12px 16px; font-weight:600; box-shadow:var(--shadow); transition:transform .06s ease, box-shadow .2s ease; }
        .btn:active{ transform: translateY(1px) }
        .btn-primary{ background:linear-gradient(135deg, var(--accent), var(--accent-2)); color:#0b1220 }
        .btn-ghost{ background:transparent; border:1px solid rgba(255,255,255,.12); color:var(--ink) }
        .grid{ display:grid; gap:16px }
        .cols-3{ grid-template-columns: repeat(3, minmax(0,1fr)); }
        @media (max-width:900px){ .cols-3{ grid-template-columns: 1fr; } }
        .card{ background:var(--panel); border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); }
        .card h3{ margin:0 0 6px; font-size:18px }
        .muted{ color:var(--muted) }
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
        .k{ display:flex; gap:8px; align-items:center; justify-content:space-between; background:#0a0d12; border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px 12px; }
        .copy{ border:0; background:rgba(255,255,255,.06); color:var(--ink); border-radius:8px; padding:6px 10px; cursor:pointer }
        .section{ margin-top:32px }
        .section h2{ font-size:24px; margin:0 0 8px }
        .code{ color:white;background:#0a0d12; border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:16px; overflow:auto }
        .pill{ display:inline-block; font-size:12px; padding:4px 8px; border-radius:999px; background:rgba(125,211,252,.15); color:#bde7ff; border:1px solid rgba(125,211,252,.35) }
        .list{ display:grid; gap:10px; }
        .list a{ text-decoration:none; color:var(--ink); background:var(--panel); padding:12px 14px; border-radius:12px; border:1px solid rgba(255,255,255,.08) }
        .list small{ color:var(--muted) }
        .sticky-head{ position:sticky; top:-1px; z-index:50; padding:12px 0; margin-top:20px; }
        .sticky-head .bar{ display:flex; gap:10px; flex-wrap:wrap; background:var(--panel); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:10px; box-shadow: var(--shadow) }
        .bar a{ color:var(--muted); text-decoration:none; font-size:14px }
        .bar a:hover{ color:var(--ink) }
        .shadowed{ box-shadow: 0 12px 18px rgba(0,0,0,.18) }
        .notice{ font-size:14px; color:var(--muted) }
        .form{ display:grid; gap:10px; max-width:520px; }
        .input, .submit{ border:1px solid rgba(255,255,255,.12); background:transparent; color:var(--ink); padding:12px 14px; border-radius:12px }
        .input:focus{ outline: 3px solid var(--ring) }
        .submit{ cursor:pointer; font-weight:700 }
        .badge{ font-size:12px; color:#0b1220; background:linear-gradient(135deg, var(--ok), #a7f3d0); border-radius:999px; padding:4px 8px; margin-left:8px }
        .tiny{ font-size: 12px; color: var(--muted) }
        pre {color:limegreen}
    </style>

    <div class="basil-page">
        <div class="wrap">
            {{-- Sticky mini-TOC --}}
            <div class="sticky-head" id="toc">
                <div class="bar" id="bar">
                    <a href="#about">About</a>
                    <a href="#try">Try</a>
                    <a href="#features">Features</a>
                    <a href="#example">Example</a>
                    <a href="#interesting">Interesting Files</a>
                    <a href="#links">Useful Links</a>
                    <a href="#newsletter">Newsletter</a>
                </div>
            </div>

            {{-- HERO --}}
            <section class="hero" id="about">
                <span class="tag mono">Basil · BASIC-inspired · Rust VM</span>
                <h1>Basil — a modern, batteries-included BASIC for today’s web & backend</h1>
                <p>
                    The ergonomics of BASIC, the reliability of Rust. Basil is a fast, embeddable language with a clean stdlib,
                    optional object libraries, and targets for CLI, CGI, and WebAssembly. Interop is a first-class citizen.
                </p>
                <div class="cta-row">
                    <a class="btn btn-primary" href="#try">Get Started</a>
                    <a class="btn btn-ghost" href="https://github.com/blackrushllc/basil" target="_blank" rel="noreferrer">View on GitHub</a>
                </div>
            </section>

            {{-- QUICK START --}}
            <section class="section" id="try">
                <h2>Try Basil in 30 seconds</h2>
                <div class="grid">
                    <div class="card">
                        <h3 class="mono">Run a sample</h3>
                        <div class="k mono" data-copy="#cmd1">
                            <code id="cmd1">cargo run -q -p basilc -- run examples/hello.basil</code>
                            <button class="copy" aria-label="Copy command">Copy</button>
                        </div>
                        <p class="tiny">Tip: add <span class="mono">--features obj-all</span> to load common object libraries.</p>
                    </div>
                    <div class="card">
                        <h3 class="mono">CGI demo</h3>
                        <div class="k mono" data-copy="#cmd2">
                            <code id="cmd2">sudo install -m755 target/release/basil-cgi /usr/lib/cgi-bin/basil.cgi</code>
                            <button class="copy" aria-label="Copy command">Copy</button>
                        </div>
                        <p class="tiny">Serve a Basil CGI page with your web server. Works great with Yore.</p>
                    </div>
                    <div class="card">
                        <h3 class="mono">Test mode</h3>
                        <div class="k mono" data-copy="#cmd3">
                            <code id="cmd3">cargo run -q -p basilc -- test examples/hello.basil</code>
                            <button class="copy" aria-label="Copy command">Copy</button>
                        </div>
                        <p class="tiny">Runs with mocked inputs and comment echoing for CI-style checks.</p>
                    </div>
                </div>
            </section>

            {{-- FEATURE HIGHLIGHTS --}}
            <section class="section" id="features">
                <h2>What makes Basil comfy & powerful</h2>
                <div class="grid cols-3">
                    <div class="card">
                        <h3>Familiar, friendly syntax</h3>
                        <p class="muted">A modern BASIC that reads cleanly and gets out of your way—great for scripts and services.</p>
                    </div>
                    <div class="card">
                        <h3>Fast Rust VM</h3>
                        <p class="muted">A lean runtime with feature flags for optional object libraries (IO, HTTP, DB, etc.).</p>
                    </div>
                    <div class="card">
                        <h3>Targets: CLI · CGI · WASM</h3>
                        <p class="muted">Build command-line tools, dynamic web pages, or run in the browser via WebAssembly.</p>
                    </div>
                    <div class="card">
                        <h3>Interop first</h3>
                        <p class="muted">FFI/WASI-friendly design to call into native code or host Basil in your stack.</p>
                    </div>
                    <div class="card">
                        <h3>Great for teaching</h3>
                        <p class="muted">Simple mental model, strong stdlib, and a “test mode” for demos and grading.</p>
                    </div>
                    <div class="card">
                        <h3>Open source</h3>
                        <p class="muted">MIT/Apache-2.0 dual license. Contributions welcome.</p>
                    </div>
                </div>
            </section>

            {{-- EXAMPLE CODE --}}
            <section class="section" id="example">
                <h2>Peek at the syntax</h2>
                <div class="code mono">
<pre>
REM Hello from Basil
DIM name$ AS INPUT$("Your name: ");
IF LEN(name$) = 0 THEN name$ = "world";

PRINT "Hello, " + name$ + "!";

REM Loop a few times
FOR i% = 1 TO 3
  PRINT "i = " + STR$(i%);
NEXT
</pre>
                </div>
                <p class="notice">Want more? Check <a href="https://github.com/blackrushllc/basil/tree/main/examples" target="_blank" rel="noreferrer">/examples</a> in the repo.</p>
            </section>

            {{-- INTERESTING FILES (edit these to match README’s “Interesting files” section) --}}
            <section class="section" id="interesting">
                <h2>Interesting files to read in this repo <span class="badge">curated</span></h2>
                <div class="list">
                    <a href="https://github.com/blackrushllc/basil/blob/main/README.md" target="_blank" rel="noreferrer">
                        <strong>README.md</strong><br><small>Overview, goals, quick start</small>
                    </a>
                    <a href="https://github.com/blackrushllc/basil/blob/main/BASIL_CGI.md" target="_blank" rel="noreferrer">
                        <strong>BASIL_CGI.md</strong><br><small>CGI usage & examples</small>
                    </a>
                    <a href="https://github.com/blackrushllc/basil/blob/main/CLASSES.md" target="_blank" rel="noreferrer">
                        <strong>CLASSES.md</strong><br><small>Objects/classes reference</small>
                    </a>
                    <a href="https://github.com/blackrushllc/basil/blob/main/FILE_IO.md" target="_blank" rel="noreferrer">
                        <strong>FILE_IO.md</strong><br><small>Modern file I/O syntax</small>
                    </a>
                    <a href="https://github.com/blackrushllc/basil/blob/main/WASM.md" target="_blank" rel="noreferrer">
                        <strong>WASM.md</strong><br><small>Build & run Basil in WebAssembly</small>
                    </a>
                </div>
                <p class="tiny">Tip: if any file names differ, just tweak the links above — this block is purely static markup.</p>
            </section>

            {{-- USEFUL LINKS (edit as you like) --}}
            <section class="section" id="links">
                <h2>Useful links</h2>
                <div class="grid">
                    <div class="card">
                        <h3>GitHub</h3>
                        <p class="muted">Star the repo, browse issues, and follow development.</p>
                        <p><a href="https://github.com/blackrushllc/basil" target="_blank" rel="noreferrer">github.com/blackrushllc/basil</a></p>
                    </div>
                    <div class="card">
                        <h3>Examples</h3>
                        <p class="muted">Working programs to learn from and copy.</p>
                        <p><a href="https://github.com/blackrushllc/basil/tree/main/examples" target="_blank" rel="noreferrer">/examples</a></p>
                    </div>
                    <div class="card">
                        <h3>Releases (optional)</h3>
                        <p class="muted">Grab binaries when releases are published.</p>
                        <p><a href="https://github.com/blackrushllc/basil/releases" target="_blank" rel="noreferrer">/releases</a></p>
                    </div>
                </div>
            </section>

            {{-- NEWSLETTER SIGNUP (wire this to your Yore controller/route) --}}
            <section class="section" id="newsletter">
                <h2>Get Basil updates</h2>
                <form class="form" method="post" action="/members')">
                    <input class="input" type="email" name="email" placeholder="you@example.com" required>
                    <button class="submit" type="submit">Subscribe</button>
                    <span class="tiny">We’ll only email for meaningful updates. Unsubscribe anytime.</span>
                </form>
            </section>

            <p class="tiny" style="margin-top:18px">
                Built with ♥ using Yore. Styling is inline for demo purposes — move to your assets pipeline when ready.
            </p>
        </div>
    </div>

    <script>
        // Smooth scroll for TOC links
        document.querySelectorAll('#toc a[href^="#"]').forEach(a=>{
            a.addEventListener('click', e=>{
                e.preventDefault();
                const id = a.getAttribute('href').slice(1);
                const el = document.getElementById(id);
                if(el){ el.scrollIntoView({ behavior:'smooth', block:'start' }); }
            });
        });

        // Sticky header shadow on scroll
        const bar = document.getElementById('bar');
        const onScroll = ()=> {
            const s = window.scrollY || document.documentElement.scrollTop;
            bar.classList.toggle('shadowed', s > 20);
        };
        onScroll(); window.addEventListener('scroll', onScroll, { passive:true });

        // Copy buttons for commands
        document.querySelectorAll('.k').forEach(box=>{
            const btn = box.querySelector('.copy');
            const sel = box.getAttribute('data-copy');
            const src = document.querySelector(sel);
            if(btn && src){
                btn.addEventListener('click', async ()=>{
                    try{
                        const text = src.textContent.trim();
                        await navigator.clipboard.writeText(text);
                        const old = btn.textContent;
                        btn.textContent = 'Copied!';
                        setTimeout(()=>btn.textContent = old, 1200);
                    }catch(e){
                        alert('Copy failed, please copy manually.');
                    }
                });
            }
        });
    </script>




