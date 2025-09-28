# Xdebug Setup Guide for Yore Framework

## Overview

Xdebug is a powerful debugging and profiling extension for PHP that allows you to step through code, inspect variables, and analyze performance. This guide covers setting up Xdebug with the Yore framework for various development scenarios.

## Quick Start

Yore provides pre-configured Xdebug commands for immediate use:

```bash
# Start development server with Xdebug debugging
composer serve:debug

# Start with profiling enabled
composer serve:profile

# Start with code coverage enabled
composer serve:coverage
```

## Xdebug Configuration

### Built-in Server Configuration

The Yore framework includes these pre-configured options:

| Command | Xdebug Mode | Port | Auto-start | Output |
|---------|-------------|------|------------|---------|
| `composer serve:debug` | debug | 9003 | Yes | IDE |
| `composer serve:profile` | profile | - | Yes | `./storage/xdebug/` |
| `composer serve:coverage` | coverage | - | No | Runtime |

### Manual Configuration

For custom setups, you can configure Xdebug manually:

#### php.ini Configuration
```ini
; Enable Xdebug
zend_extension=xdebug

; Xdebug 3.x configuration
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
xdebug.client_host=localhost

; Optional: IDE key for session filtering
xdebug.idekey=PHPSTORM

; Profiling configuration (optional)
xdebug.output_dir=/path/to/yore/storage/xdebug
```

#### Environment Variables
```bash
# Alternative to php.ini settings
export XDEBUG_MODE=debug
export XDEBUG_SESSION=1
```

## IDE Setup

### PhpStorm / IntelliJ IDEA

#### 1. Configure PHP Interpreter
1. Go to **Settings → PHP**
2. Set **PHP language level** to match your PHP version
3. Configure **CLI Interpreter** to point to your PHP executable
4. Verify Xdebug is detected in the interpreter settings

#### 2. Debug Configuration
1. Go to **Settings → PHP → Debug**
2. Set **Debug port** to `9003`
3. Check **Can accept external connections**
4. Uncheck **Force break at first line** (unless desired)
5. Check **Break at first line in PHP scripts** (optional)

#### 3. Server Configuration
1. Go to **Settings → PHP → Servers**
2. Click **+** to add a new server
3. Set:
   - **Name**: `localhost`
   - **Host**: `localhost`
   - **Port**: `8000`
   - **Debugger**: `Xdebug`
   - **Use path mappings**: Check if using Docker/remote

#### 4. Run/Debug Configuration
1. Go to **Run → Edit Configurations**
2. Click **+** → **PHP Built-in Web Server**
3. Set:
   - **Host**: `localhost`
   - **Port**: `8000`
   - **Document root**: `/path/to/yore/web`
   - **Use router script**: Unchecked

#### 5. Start Debugging
1. Set breakpoints in your code
2. Click **Start Listening for PHP Debug Connections** (phone icon)
3. Run `composer serve:debug`
4. Access your application at `http://localhost:8000`

### Visual Studio Code

#### 1. Install PHP Debug Extension
```bash
# Install the PHP Debug extension by Xdebug
code --install-extension xdebug.php-debug
```

#### 2. Configure Launch Settings
Create `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "log": true,
            "externalConsole": false,
            "pathMappings": {
                "/path/to/remote/yore": "${workspaceFolder}"
            },
            "ignore": [
                "**/vendor/**/*.php"
            ]
        },
        {
            "name": "Launch Built-in Server",
            "type": "php",
            "request": "launch",
            "program": "",
            "cwd": "${workspaceFolder}",
            "port": 9003,
            "serverReadyAction": {
                "pattern": "Development Server \\(http://localhost:([0-9]+)\\) started",
                "uriFormat": "http://localhost:%s",
                "action": "openExternally"
            }
        }
    ]
}
```

#### 3. Start Debugging
1. Set breakpoints by clicking in the gutter
2. Press **F5** or go to **Run → Start Debugging**
3. Select **Listen for Xdebug**
4. Run `composer serve:debug` in terminal
5. Access your application

### Sublime Text

#### 1. Install Xdebug Client Package
1. Install **Package Control** if not already installed
2. Install **Xdebug Client** package

#### 2. Configure Settings
Create `Xdebug.sublime-settings`:

```json
{
    "port": 9003,
    "max_children": 32,
    "max_data": 1024,
    "max_depth": 4,
    "break_on_start": false,
    "close_on_stop": true,
    "super_globals": true,
    "fullname_property": true,
    "hide_password": false,
    "path_mapping": {
        "/remote/path/to/yore": "/local/path/to/yore"
    }
}
```

#### 3. Start Debugging
1. Set breakpoints with **Ctrl+F8**
2. Start Xdebug session: **Tools → Xdebug → Start Debugging**
3. Run `composer serve:debug`

### Vim/Neovim with Vdebug

#### 1. Install Vdebug Plugin
```vim
" Using vim-plug
Plug 'vim-vdebug/vdebug'
```

#### 2. Configure Vdebug
Add to your `.vimrc`:

```vim
let g:vdebug_options = {
\    'port' : 9003,
\    'server' : 'localhost',
\    'timeout' : 20,
\    'on_close' : 'detach',
\    'break_on_open' : 1,
\    'ide_key' : '',
\    'path_maps' : {'/remote/path': '/local/path'},
\    'debug_window_level' : 0,
\    'debug_file_level' : 0,
\    'debug_file' : '',
\    'watch_window_style' : 'expanded',
\    'marker_default' : '⬦',
\    'marker_closed_tree' : '▸',
\    'marker_open_tree' : '▾'
\}
```

## Remote Debugging

### SSH Tunnel Setup

For remote debugging through SSH:

```bash
# Forward remote Xdebug port to local machine
ssh -R 9003:localhost:9003 user@remote-server

# On remote server, set these in php.ini or environment:
xdebug.client_host=localhost
xdebug.client_port=9003
```

### Docker Setup

#### docker-compose.yml
```yaml
version: '3.8'
services:
  yore:
    build: .
    ports:
      - "8000:8000"
      - "9003:9003"
    environment:
      - XDEBUG_MODE=debug
      - XDEBUG_CONFIG=client_host=host.docker.internal client_port=9003
    volumes:
      - .:/var/www/yore
    extra_hosts:
      - "host.docker.internal:host-gateway"
```

#### Dockerfile
```dockerfile
FROM php:8.1-cli

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configure Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/yore
EXPOSE 8000 9003
```

### Network Configuration

For debugging across networks:

```bash
# Find your local IP address
ip addr show | grep inet

# Set Xdebug client host to your machine's IP
XDEBUG_CONFIG="client_host=192.168.1.100" composer serve:debug
```

## Profiling

### Enabling Profiling

```bash
# Start server with profiling enabled
composer serve:profile
```

### Analyzing Profile Data

Profile files are saved to `./storage/xdebug/` with names like `cachegrind.out.12345`.

#### Using KCacheGrind (Linux/macOS)
```bash
# Install KCacheGrind
brew install qcachegrind  # macOS
sudo apt install kcachegrind  # Ubuntu

# Open profile file
kcachegrind ./storage/xdebug/cachegrind.out.12345
```

#### Using Webgrind (Web-based)
```bash
# Clone and setup Webgrind
git clone https://github.com/jokkedk/webgrind.git
cd webgrind
php -S localhost:8080

# Configure to read from your profile directory
# Edit config.php to point to ./storage/xdebug/
```

#### Using PHPStorm
1. Go to **Tools → Analyze Xdebug Profiler Snapshot**
2. Select the profile file from `./storage/xdebug/`
3. Analyze the call tree and performance metrics

## Code Coverage

### Generating Coverage Reports

```bash
# Run with coverage enabled
composer serve:coverage

# Generate coverage report with PHPUnit
vendor/bin/phpunit --coverage-html ./storage/coverage
```

### Coverage Configuration

Add to `phpunit.xml`:

```xml
<coverage>
    <include>
        <directory suffix=".php">app</directory>
        <directory suffix=".php">modules</directory>
    </include>
    <exclude>
        <directory>vendor</directory>
        <directory>storage</directory>
    </exclude>
    <report>
        <html outputDirectory="storage/coverage" lowUpperBound="50" highLowerBound="90"/>
        <text outputFile="storage/coverage.txt" showUncoveredFiles="false"/>
    </report>
</coverage>
```

## Performance Optimization

### Xdebug 3.x Optimizations

```ini
; Only enable when needed
xdebug.mode=off

; Use environment variable to enable selectively
xdebug.start_with_request=trigger

; Limit step debugging overhead
xdebug.max_nesting_level=512
xdebug.var_display_max_children=256
xdebug.var_display_max_data=1024
xdebug.var_display_max_depth=8

; Optimize profiling
xdebug.profiler_append=0
xdebug.profiler_enable_trigger=1
```

### Production Considerations

```bash
# Disable Xdebug in production
echo "xdebug.mode=off" >> /etc/php/8.1/apache2/conf.d/20-xdebug.ini

# Or remove extension entirely
rm /etc/php/8.1/mods-available/xdebug.ini
```

### Memory Management

```ini
; Increase memory limits when debugging
memory_limit=512M

; For large applications
xdebug.var_display_max_children=512
xdebug.var_display_max_data=2048
```

## Troubleshooting

### Common Issues

#### 1. Xdebug Not Connecting

**Check PHP configuration:**
```bash
php -m | grep xdebug
php --ini | grep xdebug
```

**Verify Xdebug settings:**
```bash
php -i | grep xdebug
```

**Test connection:**
```bash
# Test if port is listening
telnet localhost 9003
```

#### 2. Firewall Issues

```bash
# macOS: Allow connections on port 9003
sudo pfctl -f /etc/pf.conf

# Linux: Open port 9003
sudo ufw allow 9003
sudo iptables -A INPUT -p tcp --dport 9003 -j ACCEPT
```

#### 3. Path Mapping Issues

Ensure path mappings match between server and IDE:

```json
// VS Code launch.json
"pathMappings": {
    "/var/www/yore": "${workspaceFolder}",
    "/remote/path": "/local/path"
}
```

#### 4. Performance Issues

```bash
# Check if Xdebug is slowing down your application
time php -dxdebug.mode=off script.php
time php -dxdebug.mode=debug script.php
```

### Debug Logging

Enable Xdebug logging for troubleshooting:

```ini
xdebug.log=/tmp/xdebug.log
xdebug.log_level=7
```

### Browser Debugging

Use browser extensions to trigger debugging:

- **Chrome**: Xdebug Helper
- **Firefox**: Xdebug Helper
- **Edge**: Xdebug Helper

Set cookie: `XDEBUG_SESSION=PHPSTORM`

## Advanced Configuration

### Conditional Breakpoints

Set breakpoints that only trigger under specific conditions:

```php
// In PhpStorm, right-click breakpoint → More → Condition
$user->id === 123

// Or use xdebug_break() in code
if ($someCondition) {
    xdebug_break();
}
```

### Step Filtering

Configure IDE to skip vendor code:

```json
// VS Code settings.json
"php.debug.ignore": [
    "**/vendor/**",
    "**/node_modules/**"
]
```

### Remote Host Detection

Automatically detect remote debugging:

```php
// In your bootstrap file
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    ini_set('xdebug.client_host', $_SERVER['HTTP_X_FORWARDED_FOR']);
}
```

## Best Practices

1. **Use trigger mode** instead of always-on debugging
2. **Disable Xdebug in production** completely
3. **Use profiling selectively** on specific requests
4. **Configure step filtering** to avoid vendor code
5. **Set reasonable limits** for variable display
6. **Use conditional breakpoints** to reduce noise
7. **Monitor memory usage** when debugging large applications
8. **Use IDE-specific optimizations** for better performance

## Yore-Specific Tips

### Framework Integration

The Yore framework provides these debugging conveniences:

```php
// Use the debug module for enhanced debugging
if ($this->is_debug) {
    // Debug information is automatically available
    var_dump($this->domain, $this->site, $this->name);
}

// Leverage the multi-tenant system for environment-specific debugging
TenantResolver::debug(true); // Enable tenant resolution logging
```

### Module Debugging

Debug specific modules:

```php
// In your module's yore_module_init method
public function yore_module_init($controller, $key) {
    if ($controller->is_debug) {
        error_log("Module $key initialized");
        xdebug_break(); // Break when module loads
    }
}
```

### Template Debugging

Debug Blade templates:

```php
// In your view method
if ($this->is_debug) {
    echo "<!-- Debug: Rendering view: {$this->view_file} -->";
}
```

This comprehensive setup will give you powerful debugging capabilities for your Yore framework development!
