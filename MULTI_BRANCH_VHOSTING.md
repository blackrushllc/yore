If you would like to serve up websites simultaneously from different branches 
of the local Git repository, the cleanest way is to run each branch from its own 
working directory and let your web server route by domain to the right docroot. 

You can use Git worktrees or multiple clones, and then route different hostnames to 
those working directories. That way you can serve multiple branches of your Yore framework 
simultaneously, each one behaving like its own isolated site.

This way you can have a live `main` branch running your stable site, a `feature/some-experiment`
branch running a test site, and maybe a `clientA-impl` branch for a client-specific implementation—all 
at the same time.

Because Yore is already designed around multi-domain dispatching, this plays perfectly into our architecture. 
Instead of just swapping themes/configs within one branch, you are be able to experiment with 
different versions of Yore itself side-by-side—almost like multi-tenanting your own framework 
dev environment.

We will be setting up some tools to make this easier, such as:

A ready-to-drop Apache or Nginx config mapping branches → domains.

A script to spin up new worktrees (auto-install Composer, copy .env, etc.).

A Docker Compose sample to isolate branches completely.

# Multi-branch local vhosting strategies

Here are a few solid patterns, from simplest to more “ops-y”:

# 1) Git worktrees + per-domain vhosts (recommended)

Worktrees let one repo have multiple checked-out branches at the same time without duplicated `.git` data.

```bash
# from your main yore repo folder
git worktree add ../yore-main main
git worktree add ../yore-feature feature/some-experiment
git worktree add ../yore-clientA clientA-impl
```

Now you have:

```
~/dev/yore           # original repo (maybe unused as a docroot)
~/dev/yore-main      # branch: main
~/dev/yore-feature   # branch: feature/some-experiment
~/dev/yore-clientA   # branch: clientA-impl
```

In each worktree:

```bash
cd ../yore-main && cp .env.example .env && composer install
cd ../yore-feature && cp .env.example .env && composer install
# etc.
```

Then point domains to each worktree’s `public/` (or whatever your front controller lives in).

### Nginx example

```nginx
# Map host -> docroot
map $host $yore_root {
    default                             /home/erik/dev/yore-main/public;
    yore-feature.local.test             /home/erik/dev/yore-feature/public;
    clientA.local.test                  /home/erik/dev/yore-clientA/public;
}

server {
    listen 80;
    server_name _;  # catch all for your dev box
    root $yore_root;

    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

### Apache example (two vhosts; repeat as needed)

```apache
<VirtualHost *:80>
  ServerName main.local.test
  DocumentRoot "/home/erik/dev/yore-main/public"
  <Directory "/home/erik/dev/yore-main/public">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>

<VirtualHost *:80>
  ServerName feature.local.test
  DocumentRoot "/home/erik/dev/yore-feature/public"
  <Directory "/home/erik/dev/yore-feature/public">
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

Add `hosts` entries:

```
127.0.0.1 main.local.test feature.local.test clientA.local.test
```

### Why this is great

* Zero code changes to Yore.
* Branches truly load different code simultaneously—no weird runtime swapping.
* Fast and space-efficient because objects are shared through `.git/worktrees`.

---

# 2) Multiple clones (bare repo + linked working clones)

If you prefer isolation over efficiency:

```bash
# create a central bare repo to share objects
git clone --bare ~/dev/yore ~/dev/yore.git

# make “clones” that share the bare object store
git clone ~/dev/yore.git ~/dev/yore-main
git clone ~/dev/yore.git ~/dev/yore-feature
git -C ~/dev/yore-main checkout main
git -C ~/dev/yore-feature checkout feature/some-experiment
```

Then wire vhosts exactly as above. This duplicates `vendor/` folders (which is sometimes desirable if dependencies diverge).

---

# 3) Containers per branch + a reverse proxy (Traefik/Caddy/Nginx)

If you want maximum parity and isolation:

* Create a `docker-compose.yml` that defines one PHP-FPM + one nginx service per branch, each mounting a different worktree.
* Put Traefik (or Caddy) in front and route `Host(`feature.local.test`)` → the feature container, `Host(`clientA.local.test`)` → the clientA container, etc.
* Nice for testing different PHP versions/opcache settings per branch.

---

# 4) Fancy single-nginx “map” without separate vhost files

Already shown in #1—the `map` trick scales well as you spin up worktrees.

---

## Yore-specific tips so this stays smooth

**1) Per-domain `.env` and caches**

* Keep a separate `.env` in each worktree. Give each branch its own DB, cache, session prefix, storage path, etc.

    * Example:

      ```
      APP_NAME="Yore Main"
      DB_DATABASE=yore_main
      CACHE_PREFIX=yore_main_
      SESSION_NAME=YOREMAINSESS
      ```

      and in feature:

      ```
      APP_NAME="Yore Feature"
      DB_DATABASE=yore_feature
      CACHE_PREFIX=yore_feature_
      SESSION_NAME=YOREFEATSESS
      ```
* If you’re using OPCache locally, ensure each branch is under a different absolute path (it is, with worktrees), and in dev set:

  ```
  opcache.validate_timestamps=1
  opcache.revalidate_freq=0
  ```

  (Or run separate PHP-FPM pools per domain for stronger isolation.)

**2) Composer + autoload**

* Run `composer install` in each worktree. Don’t share `vendor/` between branches; it will bite you with mismatched autoload maps.
* If you have local path packages, keep paths relative so each worktree resolves them correctly.

**3) Migrations and seeders**

* Use distinct DBs for parallel feature testing. If you *must* share a DB, gate migrations with a feature prefix or use separate schemas, but separate DBs is simpler.

**4) Shared user uploads / artifacts**

* If you want multiple branches to see the same uploaded media, symlink the storage uploads directory to a common place:

  ```bash
  ln -s /home/erik/dev/yore-shared-uploads /home/erik/dev/yore-feature/storage/uploads
  ln -s /home/erik/dev/yore-shared-uploads /home/erik/dev/yore-main/storage/uploads
  ```

  (Or use per-branch storage if you need isolation.)

**5) Dev UX niceties**

* Make a tiny helper script:

  ```bash
  # yore-worktree.sh
  git worktree add "../yore-$1" "$2"
  cp .env.example "../yore-$1/.env"
  (cd "../yore-$1" && composer install)
  echo "Add DNS: $1.local.test -> 127.0.0.1"
  ```
* Add a post-checkout or post-merge hook in each worktree to clear Yore caches, run `composer dump-autoload`, etc.

**6) If you ever want one process to dispatch to multiple codebases (don’t)**

* PHP can’t safely load two versions of the same classes in one request space. The “one codebase routes to multiple sites” pillar is perfect for *content/config/theme* variance, but *branch code* variance should be per-docroot. That’s exactly what worktrees/containers give you.

---

## TL;DR plan

1. Use `git worktree add` to create a directory per branch.
2. Point `main.local.test`, `feature.local.test`, etc. to each worktree’s `public/`.
3. Give each worktree its own `.env`, DB, cache, and (optionally) storage.
4. Serve multiple branches simultaneously, cleanly isolated and fast.

---
Heck yes—here’s a one-command bootstrap that spins up a **Git worktree per branch**, wires a **vhost** (Apache or Nginx), runs **Composer**, and adds the **/etc/hosts** entry.

Copy this into a file like `yore-branch-site.sh`, make it executable (`chmod +x yore-branch-site.sh`), and run with sudo (only needed for vhost + hosts edits).

```bash
#!/usr/bin/env bash
# yore-branch-site.sh
# Spin up (or remove) a local site that serves a specific Git branch via its own worktree.
# Supports Apache or Nginx + PHP-FPM. Tested on Ubuntu-like systems.
set -euo pipefail

#####################################
# EDIT THESE DEFAULTS FOR YOUR BOX  #
#####################################
REPO_DIR="${REPO_DIR:-$HOME/dev/yore}"           # Your main Yore repo (has .git)
SITES_BASE="${SITES_BASE:-$HOME/dev}"            # Parent folder for created worktrees
DOCROOT_SUBDIR="${DOCROOT_SUBDIR:-public}"       # Typically "public"
DEFAULT_SERVER="${DEFAULT_SERVER:-apache}"       # apache|nginx
DEFAULT_PHP_SOCK="${DEFAULT_PHP_SOCK:-/run/php/php8.2-fpm.sock}"
APACHE_SITES_AVAIL="/etc/apache2/sites-available"
APACHE_SITES_ENABLED="/etc/apache2/sites-enabled"
NGINX_SITES_AVAIL="/etc/nginx/sites-available"
NGINX_SITES_ENABLED="/etc/nginx/sites-enabled"

usage() {
  cat <<USAGE
Usage:
  $0 create --branch <git-branch> --host <domain.local.test> [--dir <folder-name>] [--server apache|nginx] [--php-sock <sock>] [--no-composer]
  $0 remove --host <domain.local.test> [--dir <folder-name>] [--server apache|nginx]

Examples:
  sudo $0 create --branch main --host main.local.test
  sudo $0 create --branch feature/x --host feat.local.test --dir yore-feat --server nginx
  sudo $0 remove --host feat.local.test --dir yore-feat --server nginx

Env overrides:
  REPO_DIR, SITES_BASE, DOCROOT_SUBDIR, DEFAULT_SERVER, DEFAULT_PHP_SOCK
USAGE
  exit 1
}

need() { command -v "$1" >/dev/null 2>&1 || { echo "Missing required command: $1"; exit 1; }; }

ensure_hosts() {
  local host="$1"
  if ! grep -qE "^[#[:space:]]*127\.0\.0\.1[[:space:]]+$host(\$|[[:space:]])" /etc/hosts; then
    echo "Adding /etc/hosts entry for $host"
    echo "127.0.0.1 $host" | sudo tee -a /etc/hosts >/dev/null
  else
    echo "/etc/hosts already contains $host"
  fi
}

create_apache_vhost() {
  local host="$1" docroot="$2" php_sock="$3"
  sudo a2enmod rewrite >/dev/null
  # If proxy_fcgi is available, we'll wire PHP-FPM, otherwise assume mod_php
  if apache2ctl -M 2>/dev/null | grep -q proxy_fcgi; then
    sudo tee "$APACHE_SITES_AVAIL/$host.conf" >/dev/null <<CONF
<VirtualHost *:80>
  ServerName $host
  DocumentRoot "$docroot"

  <Directory "$docroot">
    AllowOverride All
    Require all granted
  </Directory>

  <FilesMatch \.php$>
    SetHandler "proxy:unix:$php_sock|fcgi://localhost/"
  </FilesMatch>

  ErrorLog \${APACHE_LOG_DIR}/$host-error.log
  CustomLog \${APACHE_LOG_DIR}/$host-access.log combined
</VirtualHost>
CONF
    sudo a2enmod proxy proxy_fcgi setenvif >/dev/null
  else
    echo "WARNING: apache proxy_fcgi not enabled; assuming mod_php."
    sudo tee "$APACHE_SITES_AVAIL/$host.conf" >/dev/null <<CONF
<VirtualHost *:80>
  ServerName $host
  DocumentRoot "$docroot"

  <Directory "$docroot">
    AllowOverride All
    Require all granted
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/$host-error.log
  CustomLog \${APACHE_LOG_DIR}/$host-access.log combined
</VirtualHost>
CONF
  fi
  sudo a2ensite "$host.conf" >/dev/null
  sudo apache2ctl configtest
  sudo systemctl reload apache2
}

remove_apache_vhost() {
  local host="$1"
  if [ -f "$APACHE_SITES_AVAIL/$host.conf" ]; then
    sudo a2dissite "$host.conf" >/dev/null || true
    sudo rm -f "$APACHE_SITES_AVAIL/$host.conf"
    sudo apache2ctl configtest
    sudo systemctl reload apache2
  fi
}

create_nginx_vhost() {
  local host="$1" docroot="$2" php_sock="$3"
  sudo tee "$NGINX_SITES_AVAIL/$host" >/dev/null <<CONF
server {
  listen 80;
  server_name $host;
  root $docroot;
  index index.php index.html;

  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location ~ \.php\$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:$php_sock;
  }

  access_log /var/log/nginx/${host}_access.log;
  error_log  /var/log/nginx/${host}_error.log;
}
CONF
  sudo ln -sf "$NGINX_SITES_AVAIL/$host" "$NGINX_SITES_ENABLED/$host"
  sudo nginx -t
  sudo systemctl reload nginx
}

remove_nginx_vhost() {
  local host="$1"
  sudo rm -f "$NGINX_SITES_ENABLED/$host" || true
  sudo rm -f "$NGINX_SITES_AVAIL/$host" || true
  sudo nginx -t && sudo systemctl reload nginx || true
}

create_site() {
  local branch="$1" host="$2" dir="$3" server="$4" php_sock="$5" run_composer="$6"

  need git
  [ -d "$REPO_DIR/.git" ] || { echo "REPO_DIR doesn't look like a git repo: $REPO_DIR"; exit 1; }

  local worktree="$SITES_BASE/$dir"
  local docroot="$worktree/$DOCROOT_SUBDIR"

  echo "Creating worktree: $worktree (branch: $branch)"
  git -C "$REPO_DIR" fetch --all --prune
  git -C "$REPO_DIR" worktree add "$worktree" "$branch"

  # Per-worktree environment & dependencies
  if [ -f "$worktree/.env" ]; then
    echo ".env already exists in worktree"
  elif [ -f "$worktree/.env.example" ]; then
    cp "$worktree/.env.example" "$worktree/.env"
    echo "Created .env from .env.example"
  else
    echo "NOTE: No .env.example found. Create $worktree/.env as needed."
  fi

  if [ "$run_composer" = "1" ]; then
    if command -v composer >/dev/null 2>&1; then
      (cd "$worktree" && composer install --no-interaction)
    else
      echo "composer not found; skipping vendor install."
    fi
  fi

  # vhost
  ensure_hosts "$host"
  mkdir -p "$docroot"
  case "$server" in
    apache) create_apache_vhost "$host" "$docroot" "$php_sock" ;;
    nginx)  create_nginx_vhost  "$host" "$docroot" "$php_sock" ;;
    *) echo "Unknown server: $server"; exit 1 ;;
  esac

  cat <<DONE

✅ Site ready!

Branch     : $branch
Worktree   : $worktree
Docroot    : $docroot
Domain     : http://$host
Server     : $server
PHP-FPM    : $php_sock

Tips:
- Give this worktree its own DB/cache in $worktree/.env (DB_DATABASE, CACHE_PREFIX, SESSION_NAME).
- If you use OPCache in dev, set: opcache.validate_timestamps=1, opcache.revalidate_freq=0
- To remove this site later:
    sudo $0 remove --host $host --dir $(basename "$worktree") --server $server
DONE
}

remove_site() {
  local host="$1" dir="$2" server="$3"
  local worktree="$SITES_BASE/$dir"

  echo "Removing vhost for $host ($server)"
  case "$server" in
    apache) remove_apache_vhost "$host" ;;
    nginx)  remove_nginx_vhost  "$host" ;;
    *) echo "Unknown server: $server"; exit 1 ;;
  esac

  # Remove hosts line (leave it if others might reuse it)
  if grep -q "$host" /etc/hosts; then
    echo "NOTE: /etc/hosts still has $host. Remove manually if you wish."
  fi

  # Remove worktree safely
  if [ -d "$worktree" ]; then
    echo "Pruning git worktree: $worktree"
    git -C "$REPO_DIR" worktree remove --force "$worktree" || true
    git -C "$REPO_DIR" worktree prune
  fi

  echo "Done."
}

# -------- arg parsing --------
[ $# -ge 1 ] || usage
cmd="$1"; shift || true

branch="" host="" dir="" server="$DEFAULT_SERVER" php_sock="$DEFAULT_PHP_SOCK" run_composer=1
while [ $# -gt 0 ]; do
  case "$1" in
    --branch) branch="$2"; shift 2 ;;
    --host) host="$2"; shift 2 ;;
    --dir) dir="$2"; shift 2 ;;
    --server) server="$2"; shift 2 ;;
    --php-sock) php_sock="$2"; shift 2 ;;
    --no-composer) run_composer=0; shift ;;
    -h|--help) usage ;;
    *) echo "Unknown arg: $1"; usage ;;
  esac
done

case "$cmd" in
  create)
    [ -n "${branch:-}" ] && [ -n "${host:-}" ] || usage
    dir="${dir:-yore-$(echo "$branch" | tr '/@:' '---')}"
    create_site "$branch" "$host" "$dir" "$server" "$php_sock" "$run_composer"
    ;;
  remove)
    [ -n "${host:-}" ] || usage
    [ -n "${dir:-}" ] || { echo "--dir is required for remove (so we delete the right worktree)"; exit 1; }
    remove_site "$host" "$dir" "$server"
    ;;
  *) usage ;;
esac
```

### Quick start (Apache)

```bash
# 1) Edit the defaults at the top if needed (REPO_DIR, PHP sock path, etc.)
# 2) Run:
sudo ./yore-branch-site.sh create --branch main --host main.local.test
sudo ./yore-branch-site.sh create --branch clientA-impl --host clienta.local.test
```

Visit `http://main.local.test` and `http://clienta.local.test`.

### Quick start (Nginx)

```bash
sudo ./yore-branch-site.sh create --branch feature/x --host feat.local.test --server nginx
```

### Remove a site

```bash
sudo ./yore-branch-site.sh remove --host feat.local.test --dir yore-feature-x --server nginx
```

---

#### Notes & niceties

* Each worktree gets its own folder (e.g., `$HOME/dev/yore-feature-x`). That keeps **OPcache, vendor autoloads, and storage** isolated.
* Tweak `.env` per worktree: give each its own **DB**, **CACHE\_PREFIX**, and **SESSION\_NAME**.
* If you share user uploads among branches, replace each worktree’s `storage/uploads` with a **symlink** to a common folder.
* If you prefer a different PHP version, just pass `--php-sock /run/php/php8.3-fpm.sock` (or set `DEFAULT_PHP_SOCK` at the top).

---
#### See /web/yore for an implementation of this script
#### To create a new site
```yore dev new-site --branch <branch> --host <domain.local.test> [--dir <folder>] [--server apache|nginx] [--php-sock <sock>] [--no-composer]```

#### To remove a site
```yore dev rm-site --host <domain.local.test> --dir <folder> [--server apache|nginx]```

# 5) Bonus: Docker Compose per branch
If you want to go all-in on isolation, you can create a `docker-compose.yml` that defines one PHP-FPM + one nginx service per branch, each mounting a different worktree. Then put Traefik (or Caddy) in front and route `Host(`feature.local.test`)` → the feature container, `Host(`clientA.local.test`)` → the clientA container, etc. Nice for testing different PHP versions/opcache settings per branch.
# Example docker-compose.yml snippet
```yaml
version: '3.8'
services:
  yore-main-php:
    image: php:8.2-fpm
    volumes:
      - ./yore-main:/var/www/html
    networks:
      - yore-net

  yore-main-nginx:
    image: nginx:latest
    volumes:
      - ./yore-main:/var/www/html
      - ./nginx.conf:/etc/nginx/nginx.conf
    ports:
      - "8080:80"
    depends_on:
      - yore-main-php
    networks:
      - yore-net

  yore-feature-php:
    image: php:8.2-fpm
    volumes:
      - ./yore-feature:/var/www/html
    networks:
      - yore-net

  yore-feature-nginx:
    image: nginx:latest
    volumes:
      - ./yore-feature:/var/www/html
      - ./nginx.conf:/etc/nginx/nginx.conf
    ports:
      - "8081:80"
    depends_on:
      - yore-feature-php
    networks:
      - yore-net
networks:
  yore-net:
    driver: bridge
```

# Example nginx.conf snippet
```nginx
worker_processes 1;
events { worker_connections 1024; }
http {
    server {
        listen 80;
        server_name main.local.test;
        root /var/www/html;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass yore-main-php:9000; # Service name from docker-compose
        }
    }

    server {
        listen 80;
        server_name feature.local.test;
        root /var/www/html;
        index index.php index.html;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass yore-feature-php:9000; # Service name from docker-compose
        }
    }
}
```

Then run `docker-compose up -d` and visit `http://main.local.test:8080` and `http://feature.local.test:8081`.

 
# ## Step 9: Troubleshooting
 - If you encounter any issues during installation, check the following:
   - Ensure that your web server has the correct permissions to read and write to the Yore directories.
   - Verify that your database credentials in the `.env` file are correct.
   - Check your web server error logs for any specific error messages.
 
