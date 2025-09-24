Yore - A Framework for Applications
```
▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
░                  ░

```

```
Copyright (C) 2025, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com
```

Please see the web/document.htm file for more information about this project and regular updates
or visit https://yobasic.com/document.htm

Please see INSTALLING.md for installation instructions and tips

MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.




# Purpose

Yore is a web framework for building applications and multi-site domain hosting.

This application is still under development with 8 fundamental pillars in mind:

+ An All-purpose lightweight MVC with JSON based configuration and a single controller class
+ Supports multiple sites and multiple domains in a single instance of the framework
+ Supports serving multiple GIT branches simultaneously (see MULTI_BRANCH_VHOSTING.md) 
dule
+ Tightly integrated Module Engine with numerous modules included
  + Create your own modules to extend the framework web and API routes and functionality
  + You can override module settings and views by creating a /modules folder in your site folder
  + Includes modules for Database, USer, Logging, Debugging, Mail and Admin
+ Versatile View Template Engine that ALSO includes Laravel Blade support:
  + Add HTML and Blade Template Directives just by creating methods in a module
  + Apply themes, permissions, constants, and other settings on a per-page, per-site and per-domain basis
  + Supports multiple themes and themes per domain
+ Debugging Integration with PhpStorm (also a PhpStorm Plugin is in the works)
+ Includes example modules and websites to get you started
+ Automatically generate complete sets of CRUD pages, forms and API routes for any database table
+ Websites can be deployed anywhere you can store data like a table or S3 bucket:
  + See /web/export.php which stuffs the entire site/multi-sites into a single JSON file or MySQL table where it can be served rather than from disk. 

## Yore is not complete!  See ROADMAP.md for planned features and modules and a TODO list


#Directory Structure

- /
  - /app
    - /Crud - Automatic CRUD page and API generator
    - /Fred - The Yore View Template Engine
    - /BladeRenderer - Laravel Blade Template Engine support (requires vendor packages)
    - /Command.php - This is the command line interface for the framework, which is the same as the web controller minus the view rendering
    - /Controller.php - This is the main controller for the whole framework, which handles all requests. You extend it with Modules which are automatically loaded.
    - /Database.php - not used, empty class, for later use 
    - /Conversions.php - not used, empty class, for later use
    - /Library.php - This is a parent class for both Command and Controller
    - /Modules.php - This is the parent class for all modules
  - /modules - All modules go in here and are automatically loaded
    - /Admin - Work-in progress Multi-Site Admin module
    - /App - Module for included sample application app.yoreweb.com
    - /Database - DB Connectivity package for all Yore modules
    - /Debug - Website Debugging tools / Debug mode, PhpStore integration
    - /Hello - Example Module with lots of comments
    - /Library - A collection of useful functions
    - /Mail - Email module
    - /Qatsi - A fully functional crytpo currency exchange and wallet system (Jk it doesn't do anything)
    - /Users - Yore Users, Login, Register, Roles, Profiles, etc
  - /pages
    - /_domains - Sites under various domain names (note the underscore in this folder name)
      - /app.yoreweb.com - example application domain
        - env.json - this is where you can override environment settings for this specific domain
        - modules.json - this is where you can enable/disable modules and override global module settings for this specific domain
        - /default - this is the "/" home page of the site
          - /views - FYI if a view is not provided, then just "@body()" is assumed
            - admin.blade.php - example of a Blade template seen by the admin user
            - homepage.html - example of an HTML template seen by user with no roles (i.e. not logged in)
            - user.blade.php - example of a Blade template seen by user with role "user"
            - (xxx.blade.php) - example of a Blade template seen by user with role "xxx"
            - (yyy.html) - example of an HTML template seen by user with role "yyy"
          - home.json - this is the config fle for the "/" home page of the site
          - page1.json - this would be the config file for "/page1", with a view file named "page1.blade.php" or "page1.html"
          - page2.json - this would be the config file for "/page2", with a view file named "page2.blade.php" or "page2.html"
        - /emails - this is the "/emails" slug/folder of the site
          - views
            - add.html - this is the view for "/emails/add"
            - delete.html - this is the view for "/emails/delete"
            - deleted.html - this is the view for "/emails/deleted"
            - edit.html - this is the view for "/emails/edit"
            - index.html - this is the view for "/emails/index"
          - add.json - this is the config file for "/emails/add"
          - delete.json - this is the config file for "/emails/delete"
          - deleted.json - this is the config file for "/emails/deleted"
          - edit.json - this is the config file for "/emails/edit"
          - index.json - this is the config file for "/emails/index"
        - /modules - this is where you can override module settings and views for this specific domain
          - /Mail
            - settings.json - this is where you can override Mail module settings for this specific domain
            - views
              - email_template.html - this is an example of overriding a module view for this specific domain
              - another_template.html - this is another example of overriding a module view for this specific domain
              - etc...
            - /Users
              - settings.json - this is where you can override Users module settings for this specific domain
              - /views
                - login.html - this is an example of overriding a module view for this specific domain
                - register.html - this is another example of overriding a module view for this specific domain
                - profile.html - this is another example of overriding a module view for this specific domain
                - etc...
        - /register... (this is the "/register" slug/folder of the site)
        - /reports... (etc .. more of the same)
        - /settings...
        - /users...
      - blackrush.us - another host etc... etc...
      - blackrushdrive.com -  etc... etc...
      - example.com -  etc... etc...
      - local -  DEFAULT WEBSITE if no matching "_domains" entry OR if using localhost OR !!!command line!!! OR CRON!!
        - /modules - this is where you can override module settings and views f no matching "_domains" entry OR if using localhost OR !!!command line!!! OR CRON!!
          - /Mail
            - settings.json - override Mail module settings if no matching "_domains" entry OR if using localhost OR !!!command line!!! OR CRON!!
    - admin - (this was supposed to be an admin module but it is not used, see modules/Admin instead)
    - default - (this was supposed to be the default site but it is not used, see _domains/local instead)
  - storage/cache/views - used by the Laravel Blade feature
  - vendor - composer packages get installed here (not in repo)
  - web
    - css - framework level css
    - images - framework level images
    - js - framework level javascript
    - themes
      - domain1
        - css
        - html - contains header, navbar & footer for all pages
        - images
        - js
      - domain2 etc..
      - somecooltheme etc..
      - default etc..
    - index.php - main entry point for everything everything


# Some other files of note:

Useful Unicode Icons!.txt - a list of unicode icons you can use in your pages

INSTALLING.MD - installation instructions

REPL.MD - Notes on the command line interface

ROADMAP.MD - planned features and TODO list (todo)

MULTI_BRANCH_VHOSTING.MD - notes on how to set up multiple git branches to be served simultaneously

LICENSE - MIT License (todo)

COLLAB.md - how to contribute to this project

# Important files in the /web folder:

  /web/cron/php - This executes all of the "Cron" methods in all modules for the current site

  /web/yore - A command line utility for managing Yore

  /web/cli.php - command line entry point

## Additional utilities and things in the /web folder:

/web/export.php - exports the entire site or all sites to a single JSON file or MySQL table

/web/document.htm - documentation file

/web/jsonviewer.php - view any JSON file in a readable format

/web/spam.php - You can send emails with this

/web/webhook.php - You can receive webhooks with this

/web/import.php - Something I was using to import webhook data

/web/enc.php - A simple encryption/decryption utility for putting sensitive data in JSON files


# Installation
See INSTALLING.md for detailed installation instructions

# Web Server Configuration



Be sure that the following lines are in your web host config

```
        <Directory /path/to/web>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
            RewriteEngine on
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php/$1 [NC,L]
        </Directory>
```

For example, for Apache your sites-available ssl config might look like this: 


```
<IfModule mod_ssl.c>
<VirtualHost *:443>
        ServerName blackrush.us
        <Directory /var/www/yore/web>
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
            RewriteEngine on
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ /index.php/$1 [NC,L]
        </Directory>

        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/yore/web
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined

SSLCertificateFile /etc/letsencrypt/live/erikleeolson.com-0001/fullchain.pem
SSLCertificateKeyFile /etc/letsencrypt/live/erikleeolson.com-0001/privkey.pem
Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>
</IfModule>


```

